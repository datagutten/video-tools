<?Php
require_once 'video.php';
require_once '../tools/exceptions.php';

class WrongDurationException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class video_duration_check extends video
{
    /**
     * Check if the duration of a file matches the given duration
     *
     * @param int $duration_check Duration to be checked in seconds
     * @param int $duration_reference Expected duration in seconds
     * @param int $tolerance Duration tolerance in seconds (default 90)
     * @return bool
     * @throws WrongDurationException
     */
    private function check_duration($duration_check, $duration_reference, $tolerance = 90)
    {
        if ($duration_reference == $duration_check) //Duration is correct
            return true;
        //Duration is within tolerance
        elseif ($duration_reference > $duration_check && $duration_reference - $duration_check <= $tolerance)
            return true;
        elseif ($duration_check > $duration_reference && $duration_check - $duration_reference <= $tolerance)
            return true;
        else {
            $msg = sprintf('Wrong duration: File duration %s is outside tolerance from %s', $duration_check, $duration_reference);
            throw new WrongDurationException($msg);
        }
    }

    /**
     * Check if the file exists and has valid duration
     * @param $file
     * @param int $duration_reference Expected duration
     * @return string File name
     * @throws FileNotFoundException
     * @throws WrongDurationException
     */
    public function check_file_duration($file, $duration_reference)
    {
        if (!file_exists($file))
            throw new FileNotFoundException($file);
        if (filesize($file) == 0) //Check if the file is empty
        {
            unlink($file);
            throw new WrongDurationException('File is empty');
        }
        try {
            $duration_file = $this->duration($file); //Get file duration
            $this->check_duration($duration_file, $duration_reference);
            return $file;
        } catch (WrongDurationException $e) {
            rename($file, $file . ".wrong_duration");
            throw $e;
        }
        catch (Exception $e) {
            rename($file, $file . ".bad_duration");
            throw new WrongDurationException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }
}