<?php

namespace Dtdi\Eventlog;

use Dtdi\Eventlog\Commands\EventlogCommand;
use Dtdi\Eventlog\Exceptions\InvalidConfiguration;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

include_once 'helpers.php';

class EventlogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
                 * This class is a Package Service Provider
                 *
                 * More info: https://github.com/spatie/laravel-package-tools
                 */
        $package
            ->name('laravel-eventlog')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(EventlogCommand::class);
    }

    public function registeringPackage()
    {
        $this->app->bind(EventLogger::class);
    }

    public static function getEventModelInstance(): Model
    {
        $eventModelClassName = self::determineEventModel();

        return new $eventModelClassName();
    }

    public static function determineEventModel(): string
    {
        $eventModel = config('eventlog.event_model') ?? Model::class;

        if (
            ! is_a($eventModel, Model::class, true)
        ) {
            throw InvalidConfiguration::modelIsNotValid($eventModel);
        }

        return $eventModel;
    }
}
