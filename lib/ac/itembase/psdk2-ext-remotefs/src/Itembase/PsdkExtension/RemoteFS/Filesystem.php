<?php
namespace Itembase\PsdkExtension\RemoteFS;

/**
 * Class Filesystem
 *
 * @package       Itembase\PsdkExtension\RemoteFS
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class Filesystem
{
    /** @var string $lastError */
    protected $lastError;

    /**
     * @param $phpValue string
     * @return int|string
     */
    protected function calculateBytes($phpValue)
    {
        $val  = trim($phpValue);
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            case 'k':
                $val *= 1024;
                break;
            case 'm':
                $val *= 1048576;
                break;
            case 'g':
                $val *= 1073741824;
                break;
        }

        return $val;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getLimits()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) != "get") {
            throw new \Exception("Unsupported request method!");
        }

        $fileSize = ini_get("upload_max_filesize");
        $postSize = ini_get("post_max_size");

        return array(
            'file_size' => $this->calculateBytes($fileSize),
            'post_size' => $this->calculateBytes($postSize),
            'max_files' => intval(ini_get("max_file_uploads"))
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function upload()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) != "post") {
            throw new \Exception("Unsupported request method!");
        }

        if (empty($_FILES)) {
            throw new \Exception("No files sent!");
        }

        if (empty($_POST['locations'])) {
            throw new \Exception("No locations sent!");
        }

        $locations         = $_POST['locations'];
        $statuses          = array();
        $currState         = -1;
        $defaultPermission = fileperms($_SERVER["DOCUMENT_ROOT"]);

        foreach ($_FILES as $hash => $file) {
            $currState++;
            $statuses[$currState] = array(
                'path'    => '',
                'hash'    => $hash,
                'success' => false,
                'reason'  => null
            );

            if (empty($locations[$hash])) {
                $statuses[$currState]['reason'] = 'Target location was not passed';
                continue;
            }

            $statuses[$currState]['path'] = rtrim($_SERVER["DOCUMENT_ROOT"], "/") . "/" . ltrim($locations[$hash], "/");

            if (!is_uploaded_file($file['tmp_name'])) {
                $statuses[$currState]['reason'] = 'Issues with uploading file! is_uploaded_file() returned false value';
                continue;
            }

            $targetDir = dirname($locations[$hash]);

            if (!file_exists($targetDir)) {
                set_error_handler(array($this, 'onWarning'));
                $status = mkdir($targetDir, $defaultPermission, true);
                restore_error_handler();

                if (!$status) {
                    $statuses[$currState]['reason'] = $this->lastError;
                    continue;
                }
            }

            $this->lastError = null;

            set_error_handler(array($this, 'onWarning'));
            $moveResult = move_uploaded_file($file['tmp_name'], $statuses[$currState]['path']);
            restore_error_handler();

            if (!$moveResult) {
                if (null == $this->lastError) {
                    $statuses[$currState]['reason'] = "Unable to store uploaded file to " . $locations[$hash];
                } else {
                    $statuses[$currState]['reason'] = $this->lastError;
                }

                continue;
            }

            $statuses[$currState]['success'] = true;
        }

        return $statuses;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function delete()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) != "post") {
            throw new \Exception("Unsupported request method!");
        }

        if (empty($_POST['fileList'])) {
            throw new \Exception("File list is not provided!");
        }

        $statuses      = array();
        $currentStatus = -1;

        foreach ($_POST['fileList'] as $hash => $filePath) {
            $currentStatus++;
            $statuses[$currentStatus] = array(
                'path'    => $filePath,
                'hash'    => $hash,
                'success' => false,
                'reason'  => null
            );

            if (!file_exists($filePath)) {
                $statuses[$currentStatus] = "File doesn't exists";
                continue;
            }

            set_error_handler(array($this, 'onWarning'));
            $isDeleted = unlink($filePath);
            restore_error_handler();

            if (!$isDeleted) {
                $statuses[$currentStatus] = $this->lastError;
                continue;
            }

            $statuses[$currentStatus]['success'] = true;
        }

        return $statuses;
    }

    /**
     * @throws \Exception
     */
    public function deleteFolder()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) != "post") {
            throw new \Exception("Unsupported request method!");
        }

        $statuses      = array();
        $currentStatus = -1;

        foreach ($_POST['dirList'] as $hash => $dirPath) {
            $currentStatus++;
            $statuses[$currentStatus] = array(
                'path'    => $dirPath,
                'hash'    => $hash,
                'success' => false,
                'reason'  => null
            );

            if (!is_dir($dirPath)) {
                $statuses[$currentStatus]['reason'] = "Provided path is not a directory";
                continue;
            }

            $this->removeDirectory($dirPath);

            $statuses[$currentStatus]['success'] = true;
        }

        return $statuses;
    }

    /**
     * @param $path
     */
    protected function removeDirectory($path)
    {
        if (!is_dir($path)) {
            return;
        }

        $objects = scandir($path);

        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($path . "/" . $object) == "dir") {
                    $this->removeDirectory($path . "/" . $object);
                } else {
                    unlink($path . "/" . $object);
                }
            }
        }

        reset($objects);
        rmdir($path);
    }

    /**
     * Custom PHP error handling
     *
     * @param $errno  int
     * @param $errstr string
     */
    public function onWarning($errno, $errstr)
    {
        $this->lastError = sprintf("%s (%d)", $errstr, $errno);
    }
}
