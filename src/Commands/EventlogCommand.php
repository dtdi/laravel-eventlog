<?php

namespace Dtdi\Eventlog\Commands;

use Dtdi\Eventlog\Exporter\OCEL1;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class EventlogCommand extends Command
{
  public $signature = 'pm:dump';

  public $description = 'Dump the event log to an OCEL XML file file';

  public function handle(): int
  {
    $this->comment('Started');

    $bar = $this->output->createProgressBar();

    $logPath = eventlog()
      ->withBar($bar)
      ->setupForSnipeIt()
      ->modifyBuilder(function (Builder $builder) {
      })
      ->setLogExporter(new OCEL1)->write();

    $bar->finish();

    $this->comment('Finished');

    $this->info('log was written to ' . $logPath);

    return self::SUCCESS;
  }
}
