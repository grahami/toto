<?php
/*
 * A helper class to write standardised event messages to a log file. These are json encoded to make them easy for
 * something like LogStash to consume into ElasticSearch
 */

namespace App\Helpers;

use App\Models\User;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Request;
use Config;
use Session;
use Auth;
use Log;

class EventHelper
{

    /**
     * Standard log method for logging event data
     * @param string $event The type of event
     * @param string $level The event level (DEBUG,TRACE,QUERY,WARN,AUDIT,INFO,ERROR,FATAL)
     * @param array $data The data portion of the event that is specific to that event
     * @param array $standardData The data portion of the event that is standardised across all events
     */
    public static function log($event, $level, $data, $standardData = array())
    {
        // check if the $level is currently enabled. FATAL, ERROR, AUDIT and INFO are always enabled
        $level = strtoupper($level);

        $eventArray = array();

        // see if logging for the level is enabled
        if (!self::isEnabled($level)) {
            return;
        }

        // create a MonoLog instance for file handling
        $log = new Logger('event');

        // write audit messages to their own file, as opposed to more general event messages
        if ($level == 'AUDIT') {
            $logFile = Config::get('app.event_log_path') . '/audit_' . date('Y_m_d') . '.audit';
        } else {
            $logFile = Config::get('app.event_log_path') . '/event_' . date('Y_m_d') . '.log';
        }

        // see if the user is logged in - but avoid an recursive loop for QUERY events since checking logged in can
        // involve database access
        if (Auth::check() && $level != 'QUERY') {
            $user = User::getAuthUser();
            if (isset($user)) {
                $eventArray['data']['user'] = $user->id . '-' . $user->name;
            }
        }

        // initialise the handler with the name of the event file (one per day)
        $handler = new StreamHandler($logFile);

        // set the formatter so that we only log the information that we want for events and
        // no other generic log information
        $handler->setFormatter(new LineFormatter("%message%\n"));

        // add the handler to the logger so we can use it
        $log->pushHandler($handler);

        //Get information about the class, method, file and line of the calling code
        //We can't know whether this function will be called in procedural or object style or how deep
        //the call stack will be but we're aiming for class::method(file:line) or the best we can do
        $callStack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = '';
        if (isset($callStack[1]['class'])) {
            // remove the full classpath and just use the class name
            $class = $callStack[1]['class'];
            $lastSlash = strrpos($class, '\\');
            if ($lastSlash !== false) {
                $class = substr($class, $lastSlash + 1);
            }
            $caller .= $class;
            if (isset($callStack[1]['function'])) {
                $caller .= '::' . $callStack[1]['function'];
            }
        }
        if (isset($callStack[0]['file']) && isset($callStack[0]['line'])) {
            //Use $ as the path separator to keep the encoding clean
            $caller .= '(' . str_replace('/', '$', $callStack[0]['file']) . ':' . $callStack[0]['line'] . ')';
        }

        $eventArray['event'] = $event;
        $eventArray['level'] = $level;
        $eventArray['caller'] = $caller;
        $eventArray['host'] = gethostname();
        $eventArray['environment'] = app()->environment();
        $eventArray['app'] = Config::get('app.name');
        $eventArray['pid'] = getmypid();
        $eventArray['reqRemoteHost'] = Request::ip();
        $eventArray['time'] = DateTimeHelper::getIso8601ZuluTime(true, 3);

        //Set the data element with each event specific element prefixed with the event name to avoid
        //namespace and datatype conflicts if using ElasticSearch to process log files
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $eventArray['data'][$event . '_' . $key] = get_class($value) . ':' . print_r(get_object_vars($value),
                        true);
            } else {
                $eventArray['data'][$event . '_' . $key] = print_r($value, true);
            }
        }

        //Set the data element with any standardData elements (these are standardised across all namespaces)
        foreach ($standardData as $key => $value) {
            if (is_object($value)) {
                $eventArray['data'][$key] = get_class($value) . ':' . print_r(get_object_vars($value), true);
            } else {
                $eventArray['data'][$key] = print_r($value, true);
            }
        }

        //utf8 and json encode the entire event array
        array_walk_recursive($eventArray, function (&$item, $key) {
            $item = utf8_encode($item);
        });
        $eventJson = json_encode($eventArray, JSON_NUMERIC_CHECK) . "\n";

        // now write the event message out
        $log->addInfo($eventJson);
    }

    /**
     * Checks if logging events is enabled for the log level.
     * FATAL, ERROR, INFO and AUDIT are always considered enabled
     * If the log level is not recognised then logging is not enabled
     *
     * @param string $level The log level to check if enabled
     * @return bool
     */
    protected static function isEnabled($level)
    {
        if ($level == 'FATAL' || $level == 'ERROR' || $level == 'AUDIT' || $level == 'INFO') {
            return true;
        }

        $returnVal = self::isLogLevel($level);
        return $returnVal;
    }

    protected static function isLogLevel($level)
    {
        $returnVal = false;
        $logLevel = Config::get('app.event_log_level', '');

        $logFlags = [
            'WARN' => 'W',
            'TRACE' => 'T',
            'DEBUG' => 'D',
            'INFO' => 'I',
            'QUERY' => 'Q',
        ];
        if (isset($logFlags[$level]) && strpos($logLevel, $logFlags[$level]) !== false) {
            $returnVal = true;
        }
        return $returnVal;
    }
}