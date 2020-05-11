<?php

const FILE_NAME = 'logs.zip';

class Log
{
    public $filename;
    public $finishText;

    protected $zip;
    protected $dataProcessings = [];

    public function __construct()
    {
        $this->filename = __DIR__ . DIRECTORY_SEPARATOR . FILE_NAME;

        $this->zip = new \ZipArchive();

        if ($this->zip->open($this->filename, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE) !== TRUE) {
            die ("An error occurred creating your ZIP file.");
        }
    }

    /**
     * @param string $type
     * @return string
     */
    public static function getName(string $type): string
    {
        $time = date('H:i:s');

        return "{$type}/{$type}_{$time}.log";
    }

    /**
     * @param string $command
     * @return string
     */
    public static function commandRunAndOutput(string $command): string
    {
        exec($command, $outputs);

        $outputs = implode(PHP_EOL, $outputs);

        return $outputs;
    }

    /**
     * @param Closure $closure
     */
    public function addDataProcessing(\Closure $closure) {
        $this->dataProcessings[] = $closure->bindTo($this, __CLASS__);
    }

    public function run()
    {
        foreach ($this->dataProcessings as $callback) {
            $callback();
        }

        $this->finishText = $this->finishText ?: "Numfiles: {$this->zip->numFiles}\n";
    }

    public function outputFinishText()
    {
        echo PHP_EOL, $this->finishText;
    }

    public function __destruct()
    {
        $this->zip->close();
    }
}

/**
 * Code execution
 */
$limit_iteration = $argv[1] ?? 50;
$commands = [
    'proccess' => 'ps aux',
    'netstat' => 'netstat',
];

$log = new Log;
$log->addDataProcessing(function () use ($limit_iteration, $commands) {
    for ($i = 0; $i < $limit_iteration; $i++) {
        foreach ($commands as $dir => $command) {
            $this->zip->addFromString(Log::getName($dir), Log::commandRunAndOutput($command));
        }
        echo '...' . floor($i / $limit_iteration * 100) . '%';
        sleep(1);
    }

//    $this->finishText = "Test finish text: {$this->zip->numFiles}\n";
});
$log->run();
$log->outputFinishText();

