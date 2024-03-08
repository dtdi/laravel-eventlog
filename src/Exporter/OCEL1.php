<?php

namespace Dtdi\Eventlog\Exporter;

use Carbon\Carbon;
use Dtdi\Eventlog\Contracts\Exporter;
use Dtdi\Eventlog\EventAttributeBag;
use Dtdi\Eventlog\ObjectAttributeBag;
use SimpleXMLElement;

class OCEL1 implements Exporter
{
    /**
     * specifies the version, attribute names, and object types that compose
     * the log.
     */
    protected SimpleXMLElement $globalLog;

    protected SimpleXMLElement $globalEvent;

    protected SimpleXMLElement $globalObject;

    protected SimpleXMLElement $listAttributeNames;

    protected array $attributesNames = [];

    protected SimpleXMLElement $listObjectTypes;

    protected SimpleXMLElement $events;

    protected SimpleXMLElement $objects;

    protected array $objectIds = [];

    protected array $objectTypes = [];

    protected SimpleXMLElement $xml;

    public function __construct()
    {
        $this->xml = new \SimpleXMLElement('<log></log>');
        $xml = $this->xml;

        $this->globalLog = $xml->addChild('global');
        $this->globalLog->addAttribute('scope', 'log');

        $version = $this->globalLog->addChild('string');
        $version->addAttribute('key', 'version');
        $version->addAttribute('value', '0.1');

        $this->listAttributeNames = $this->globalLog->addChild('list');
        $this->listAttributeNames->addAttribute('key', 'attribute-names');

        $this->listObjectTypes = $this->globalLog->addChild('list');
        $this->listObjectTypes->addAttribute('key', 'object-types');

        $this->globalEvent = $xml->addChild('global');
        $this->globalEvent->addAttribute('scope', 'event');

        foreach (['id', 'activity', 'timestamp', 'omap'] as $value) {
            $elem = $this->globalEvent->addChild('string');
            $elem->addAttribute('key', $value);
            $elem->addAttribute('value', '__INVALID__');
        }

        $this->globalObject = $xml->addChild('global');
        $this->globalObject->addAttribute('scope', 'object');

        foreach (['id', 'type'] as $value) {
            $elem = $this->globalObject->addChild('string');
            $elem->addAttribute('key', $value);
            $elem->addAttribute('value', '__INVALID__');
        }

        $this->events = $xml->addChild('events');
        $this->objects = $xml->addChild('objects');
    }

    /**
     * @param  ObjectAttributeBag  $objects
     */
    public function addEvent(EventAttributeBag $eventBag, ?array $objects)
    {
        $eventElem = $this->events->addChild('event');
        $id = $eventElem->addChild('string');
        $id->addAttribute('key', 'id');
        $id->addAttribute('value', $eventBag->id);

        $id = $eventElem->addChild('string');
        $id->addAttribute('key', 'activity');
        $id->addAttribute('value', $eventBag->eventName);

        $id = $eventElem->addChild('date');
        $id->addAttribute('key', 'timestamp');
        $id->addAttribute('value', $eventBag->timestamp->toJSON());

        $vmap = $eventElem->addChild('list');
        $vmap->addAttribute('key', 'vmap');

        foreach ($eventBag->attributes as $attribute => $value) {

            $type = gettype($value);

            if (is_a($value, Carbon::class)) {
                $value = $value->toJSON();
            }

            if ($value && $type) {

                $a = $vmap->addChild($type);
                $a->addAttribute('key', $attribute);
                $a->addAttribute('value', $value);

                if (! in_array($attribute, $this->attributesNames)) {
                    $this->attributesNames[] = $attribute;
                }
            }
        }

        $omap = $eventElem->addChild('list');
        $omap->addAttribute('key', 'omap');

        foreach ($objects as $object) {
            $a = $omap->addChild('string');
            $a->addAttribute('key', 'object-id');
            $a->addAttribute('value', $object->objectId);
            $this->addObject($object);
        }
    }

    public function addObject(ObjectAttributeBag $object)
    {
        $objectID = $object->objectId;
        $objectRelationType = $object->objectRelationType;

        if (in_array($objectID, $this->objectIds)) {
            return;
        }
        $this->objectIds[] = $objectID;

        if (! in_array($objectRelationType, $this->objectTypes)) {
            $this->objectTypes[] = $objectRelationType;
        }

        $objectElem = $this->objects->addChild('object');
        $id = $objectElem->addChild('string');
        $id->addAttribute('key', 'id');
        $id->addAttribute('value', $objectID);
        $id = $objectElem->addChild('string');
        $id->addAttribute('key', 'type');
        $id->addAttribute('value', $objectRelationType);

        $ovmap = $objectElem->addChild('list');
        $ovmap->addAttribute('key', 'ovmap');

        foreach ($object->attributes as $key => $value) {
            if ($value) {
                $ovval = $ovmap->addChild('type');
                $ovval->addAttribute('key', $key);
                $ovval->addAttribute('value', $value);
            }
        }
    }

    public function writeToFile(string $path): void
    {
        foreach ($this->objectTypes as $name) {
            $elem = $this->listObjectTypes->addChild('string');
            $elem->addAttribute('key', 'type');
            $elem->addAttribute('value', $name);
        }

        foreach ($this->attributesNames as $name) {
            $elem = $this->listAttributeNames->addChild('string');
            $elem->addAttribute('key', 'name');
            $elem->addAttribute('value', $name);
        }

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->xml->asXML());
        $dom->save($path);
    }
}
