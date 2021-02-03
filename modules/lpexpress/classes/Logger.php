<?php

class Logger
{
    protected $options = [
        'extension' => '',
        'fileName' => '',
        'date_format' => 'Y-m-d',
        'time_format' => 'G:i:s.u',
        'prefix' => 'log_',
        'append' => true,
        'append_context' => true,
        'default_directory_permissions' => '0755',
        'flush' => true, // Update file content after each write (not save anything in buffer)
        'create_file_on_awake' => true, // This create new file on instance __constructor method, else create new file on demand.
    ];

    protected $log_level = [
        'ERROR' => 'ERROR',
        'WARNING' => 'WARNING',
        'INFO' => 'INFO',
    ];

    private $log_directory;
    private $file_handle;
    private $log_file_path;

    private $title;
    private $max_level_length;

    private $file_created = false;

    public function __construct($log_directory, $options = [])
    {
        $this->getLevelStrLength();

        $this->log_directory = $log_directory;
        $this->options = array_merge($this->options, $options);

        $this->setLogFilePath($log_directory);

        if (!file_exists($this->log_directory))
        {
            if (!mkdir($log_directory, $this->options['default_directory_permissions'], true))
            {
                throw new RuntimeException('Could not create directory to store logs. Check permissions and log path.');
            }
        }

        if (file_exists($this->log_file_path) && !is_writable($this->log_file_path))
        {
            throw new RuntimeException('Could not be writer to file. Check permissions of this file.');
        }

        if ($this->options['create_file_on_awake'])
        {
            if ($this->options['append'])
            {
                $this->setFileHandle('a');
            }
            else
            {
                $this->setFileHandle('w+');
            }

            if (!$this->file_handle)
            {
                throw new RuntimeException('Could not open file. Check permissions.');
            }
            $this->file_created = true;
        }
    }

    protected function setLogFilePath($log_directory)
    {
        if ($this->options['fileName'])
        {
            $path = $log_directory.DIRECTORY_SEPARATOR.$this->options['fileName'];
            if ($this->options['extension'] && strpos($this->options['fileName'], $this->options['extension']) == false)
            {
                $path .= '.'.$this->options['extension'];
            }
        }
        else
        {
            $path = $log_directory.DIRECTORY_SEPARATOR.$this->options['prefix'].date($this->options['date_format']);
            if ($this->options['extension'])
            {
                $path .= '.'.$this->options['extension'];
            }
        }
        $this->log_file_path = $path;
    }

    protected function setFileHandle($write_mode)
    {
        $this->file_handle = fopen($this->log_file_path, $write_mode);
    }

    public function __destruct()
    {
        if ($this->file_handle)
        {
            fclose($this->file_handle);
        }
    }

    protected function getLevelStrLength()
    {
        $max = 0;
        foreach ($this->log_level as $level)
        {
            if (strlen($level) > $max)
            {
                $max = strlen($level);
            }
        }
        $this->max_level_length = $max;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function info($message, $context = [])
    {
        $this->write($message, $this->log_level['INFO'], $context);
    }

    public function warning($message, $context = [])
    {
        $this->write($message, $this->log_level['WARNING'], $context);
    }

    public function error($message, $context = [])
    {
        $this->write($message, $this->log_level['ERROR'], $context);
    }

    protected function write($message, $level, $context = [])
    {
        if (!$this->file_created)
        {
            if ($this->options['append'])
            {
                $this->setFileHandle('a');
            }
            else
            {
                $this->setFileHandle('w+');
            }
            if (!$this->file_handle)
            {
                throw new RuntimeException('Could not open file. Check permissions.');
            }
            $this->file_created = true;
        }

        if (!$this->file_handle)
        {
            return false;
        }

        if (!fwrite($this->file_handle, $this->formatMessage($message, $level, $context)))
        {
            throw new RuntimeException('Can\'t write to file. Check permissions of file.');
        }

        if ($this->options['flush'])
        {
            fflush($this->file_handle);
        }

        return true;
    }

    protected function formatMessage($message, $level, $context = [])
    {
        $message = [
            'date' => date($this->options['time_format']),
            'title' => $level,
            'padding' => str_repeat(' ', $this->max_level_length - strlen($level)),
            'message' => $message,
            'context' => ''
        ];

        if (!empty($this->title))
        {
            $message['title'] = $this->title.'.'.$level;
        }


        if ($this->options['append_context'] && !empty($context))
        {
            $message['context'] = json_encode($context);
        }

        return join(' ', $message).PHP_EOL;
    }
}
