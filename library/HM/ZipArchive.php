<?php
class HM_ZipArchive extends ZipArchive
{
    public function extractTo($destination, $subdir = null)
    {
      $errors = array();

      // Prepare dirs
      $destination = str_replace(array("\\"), DIRECTORY_SEPARATOR, $destination);
      if (substr($destination, strlen(DIRECTORY_SEPARATOR) * -1) != DIRECTORY_SEPARATOR)
        $destination .= DIRECTORY_SEPARATOR;


      // Extract files
      for ($i = 0; $i < $this->numFiles; $i++)
      {
        $filename = $this->getNameIndex($i);

        {
          $relativePath = $filename;//substr($filename, strlen($subdir));
          $relativePath = str_replace(array("\\"), DIRECTORY_SEPARATOR, $relativePath);
          if (strlen($relativePath) > 0)
          {
            if (substr($filename, -1) == "/")  // Directory
            {
              // New dir
              if (!is_dir($destination . $relativePath))
                if (!@mkdir($destination . $relativePath, 0755, true))
                  $errors[$i] = $filename;
            }
            else
            {
              if (dirname($relativePath) != ".")
              {
                if (!is_dir($destination . dirname($relativePath)))
                {
                  // New dir (for file)
                  @mkdir($destination . dirname($relativePath), 0755, true);
                }
              }

              // New file
              if (@file_put_contents($destination . $relativePath, $this->getFromIndex($i)) === false)
                $errors[$i] = $filename;
            }
          }
        }
      }

      return !count($errors);
    }
  }
?>