<?php

namespace Dtdi\Eventlog;

use Closure;
use Dtdi\Eventlog\Contracts\Exporter as ExporterContract;
use Dtdi\Eventlog\Exporter\OCEL1;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EventLogger
{
    protected ?Model $eventModel = null;

    protected ?ExporterContract $exporter = null;

    protected Builder $builder;

    protected \Symfony\Component\Console\Helper\ProgressBar $progressbar;

    public ?Closure $defaultEventResolver = null;

    public ?Closure $defaultObjectResolver = null;

    protected array $eventDefinitions = [];

    protected array $objectRelations = [];

    protected array $objects = [];

    protected string $caseRelation = 'item';

    protected string $eventName;

    public function __construct(Repository $config)
    {

        $this->eventName = $config->get('eventlog.event_name', 'activity_name');

        $this->eventModel = EventlogServiceProvider::getEventModelInstance();
        $this->builder = $this->eventModel->query();

        $this->setDefaultEventAttributeResolver(function (string $eventName, Model|array $model): EventAttributeBag {
            $eventId = $model['id'];
            $ts = $model['created_at'];

            if (is_a($model, Model::class)) {
                $attributes = Arr::dot($model->toArray());
            } else {
                $attributes = Arr::dot($model);
            }

            return new EventAttributeBag($eventId, $eventName, $ts, $attributes);
        });

        $this->setDefaultObjectAttributeResolver(function (string $relationType, Model|array $model): ObjectAttributeBag {
            $objectId = $model['id'];

            if (is_a($model, Model::class)) {
                $attributes = Arr::dot($model->toArray());
                $objectType = strtolower((new \ReflectionClass($model))->getShortName());
                $attributes['obj.type'] = $objectType;
                $attributes['obj.relationType'] = $relationType;
                $attributes['obj.id'] = $relationType.'_'.$objectType.'_'.$objectId;
            } else {
                $attributes = Arr::dot($model);
            }

            return new ObjectAttributeBag($attributes['obj.id'], $relationType, $attributes);
        });
    }

    public function setupForSnipeIt(): static
    {
        $this->setDefaultEventAttributeResolver(function (string $eventName, Model $model) {
            $eventId = $model->id;
            $ts = $model->created_at;

            $attributes = $model->only([
                'note',
                'filename',
                'expected_checkin',
                'accept_signature',
                'log_meta',
                'action_date',
                'action_source',
            ]);

            return new EventAttributeBag($eventId, $eventName, $ts, $attributes);
        });

        $this->setDefaultObjectAttributeResolver(function (string $relationType, Model $model): ObjectAttributeBag {
            $objectId = $model['id'];

            $objectType = strtolower((new \ReflectionClass($model))->getShortName());
            if ($objectType == 'asset') {
                $attributes =
                  (Arr::only(
                      Arr::dot($model->load('model', 'assetstatus', 'model.manufacturer')->toArray()),
                      [
                          'name',
                          'assetstatus.name',
                          'asset_tag', 'assigned_to',
                          'notes', 'physical', 'requestable', 'accepted', 'last_checkout',
                          'last_checkin', 'expected_checkin', 'checkin_counter', 'checkout_counter',
                          'requests_counter', 'model.name', 'model.manufacturer.name',
                      ]
                  ));
            } elseif (in_array($objectType, ['user', 'admin'])) {
                $attributes = (Arr::only((Arr::dot($model->toArray())), ['first_name', 'last_name', 'email']));
            } elseif (in_array($objectType, ['location'])) {
                $attributes = (Arr::only((Arr::dot($model->toArray())), ['name']));
            }

            $attributes['obj.type'] = $relationType.'_'.$objectType;
            $attributes['obj.relationType'] = $relationType;
            $attributes['obj.id'] = $relationType.'_'.$objectType.'_'.$objectId;

            return new ObjectAttributeBag($attributes['obj.id'], $attributes['obj.type'], $attributes);
        });

        $this->addObjectRelation('admin');
        $this->addObjectRelation('item');
        $this->addObjectRelation('location');
        $this->addObjectRelation('target');

        return $this;
    }

    public function addEventDefinition(Closure $eventAttributeResolver, string $eventName = '*'): static
    {
        $this->eventDefinitions[$eventName] = $eventAttributeResolver;

        return $this;
    }

    public function setDefaultEventAttributeResolver(Closure $eventAttributeResolver): static
    {
        $this->defaultEventResolver = $eventAttributeResolver;

        return $this;
    }

    public function setDefaultObjectAttributeResolver(Closure $objectAttributeResolver): static
    {
        $this->defaultObjectResolver = $objectAttributeResolver;

        return $this;
    }

    public function addObjectRelation(string $relation, ?Closure $objectAttributeResolver = null): static
    {
        $this->objectRelations[$relation] = $objectAttributeResolver;

        return $this;
    }

    public function withBar($bar): static
    {
        $this->progressbar = $bar;

        return $this;
    }

    public function setLogExporter(ExporterContract $logExporter): static
    {
        $this->exporter = $logExporter;

        return $this;
    }

    public function write($path = 'log.xmlocel')
    {

        if ($this->progressbar) {
            $this->progressbar->start($this->eventModel::count());
        }

        $exporter = $this->exporter ? $this->exporter : new OCEL1();

        foreach ($this->builder->with(array_keys($this->objectRelations))->lazy() as $eventObj) {
            if ($this->progressbar) {
                $this->progressbar->advance();
            }

            $eventName = $eventObj[$this->eventName];
            $eventResolver = array_key_exists($eventName, $this->eventDefinitions) ? $this->eventDefinitions[$eventName] : $this->defaultEventResolver;
            $eventBag = $eventResolver($eventName, $eventObj);

            $objects = [];
            foreach ($this->objectRelations as $relation => $resolver) {
                if ($eventObj[$relation]) {
                    $objectType = strtolower((new \ReflectionClass($eventObj[$relation]))->getShortName());
                    $cache_key = $relation.'_'.$objectType.'_'.$eventObj[$relation]->id;
                    if (! array_key_exists($cache_key, $this->objects)) {

                        $objectResolver = $resolver ? $resolver : $this->defaultObjectResolver;
                        $objectBag = $objectResolver($relation, $eventObj[$relation]);
                        $this->objects[$cache_key] = $objectBag;
                    } else {
                        $objectBag = $this->objects[$cache_key];
                    }
                    $objects[$relation] = $objectBag;
                }
            }

            $exporter->addEvent($eventBag, $objects);
        }
        $out = storage_path($path);
        $exporter->writeToFile($out);

        return $out;
    }
}
