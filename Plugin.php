<?php namespace Octobro\BackupLog;

use Artisan, Backend;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use Octobro\BackupLog\Models\BackupLog;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'BackupLog',
            'description' => 'No description provided yet...',
            'author' => 'octobro',
            'icon' => 'icon-leaf'
        ];
    }

    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        //
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        //
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Octobro\BackupLog\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * registerPermissions used by the backend.
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'octobro.backuplog.some_permission' => [
                'tab' => 'BackupLog',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
        return [
            'backup_logs' => [
                'label'       => 'backup Log',
                'description' => "View Backup log with their recorded time and details.",
                'category'    => SettingsManager::CATEGORY_LOGS,
                'icon'        => 'icon-database',
                'url'         => Backend::url('octobro/backuplog/backuplogs'),
                'permissions' => [],
                'order'       => 1002,
            ],
        ];
    }

    public function registerSchedule($schedule)
    {
        $this->scheduleBackupDatabase($schedule);
    }

    protected function scheduleBackupDatabase($schedule)
    {
        $backup_trigger_function = env('BACKUP_LOG_TRIGGER_FUNCTION', 'dailyAt');
        $backup_trigger_at       = env('BACKUP_LOG_TRIGGER_AT', '23:59');
        $backup_trigger_in_day   = env('BACKUP_LOG_TRIGGER_IN_DAY');
        $backup_trigger_override = env('BACKUP_LOG_OVERRIDE_SCHEDULE', false);
        $backup_is_today         = filled($backup_trigger_in_day) ? today()->firstOfMonth()->addDays($backup_trigger_in_day)->isToday() : today()->endOfMonth()->isToday();

        $schedule
        ->call(fn() => $this->runBackupCommand('backup:run --only-db'))
        ->when(boolval($backup_is_today) || boolval($backup_trigger_override))
        ->{$backup_trigger_function}($backup_trigger_function == 'dailyAt' ? $backup_trigger_at : null);
    }

    protected function runBackupCommand($command)
    {
        if(strpos($command, 'run') !== false){
            $filename = 'octobro_backup_db_' . now()->format('jMy_H-i') . '.zip';
            $command  = sprintf('%s --filename=%s', $command, $filename);
        }

        Artisan::call($command);
        BackupLog::record($filename);
    }

}
