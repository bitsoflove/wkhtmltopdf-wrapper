<?php

namespace BitsOfLove;

class PdfLib {

    public $params;
    private $maxTmpFileAge;

    public function __construct($params=null)
    {
        //currently not using any params, but hey, you never know right?
        $this->params=$params;
        $this->maxTmpFileAge = 2*24*60*60; //two days
    }


    /**
     * Validates input, executes custom wkhtmltopdf statement and returns result or error
     */
    public function generate(array $options) {

        //cleanup old files and validate options
        $this->clearTmpFolder();
        $this->validateOptions($options);

        //set some default variables when they are not provided
        $options['name'] = empty($options['name']) ? 'pdf' : $options['name'];

        //initialize: create tmp files and build the bash statement
        $locations = $this->createTemporaryHtmlFiles($options);
        $statement = $this->buildStatement($locations);

        //execute the statement
        $output = $this->executeStatement($statement);

        //parse output and return result, or handle error
        if($this->isValidOutput($output)) {
            $this->returnResult($statement, $options, $locations);
        } else {
            $this->handleError($output);
        }
    }

    /**
     * Determine wether the executed statement has been executed successfully
     * Basically any non-zero return value is an error.
     * Common error codes: http://www.linuxtopia.org/online_books/advanced_bash_scripting_guide/exitcodes.html
     *
     * Via http://stackoverflow.com/a/2230620/237739
     */
    private function isValidOutput($output) {
        if(is_array($output) && isset($output['exitCode']) && is_int($output['exitCode'])) {
            return ($output['exitCode'] === 0);
        }

        return false;
    }


    /**
     * Based on the $options, return a link to the PDF or just push the pdf
     */
    private function returnResult($statement, $options, $locations) {
        if(!empty($options['immediateDownload'])) {
            $this->pushPdfDownload($statement, $options, $locations);
        } else {
            return $locations['relativepdf'];
        }
    }

    private function handleError($output) {
        echo json_encode($output);die;
    }

    /**
     * Execute the bash statement and return its output + exit code
     * (This only works on unix filesystems)
     *
     * http://stackoverflow.com/a/5602987/237739
     */
    private function executeStatement($statement) {
        $output = array();
        $exitCode = 0;
        exec($statement . ' 2>&1', $output, $exitCode);

        return array('output' => $output,
                     'exitCode' => $exitCode);
    }

    private function pushPdfDownload($statement, $options, $locations) {

        $pdf = $locations['pdf'];
        $name = $options['name'];

        //grab the contents of the statement and send it to the user
        $str = file_get_contents($pdf);
        header('Content-Type: application/pdf');
        header('Content-Length: '.strlen($str));
        header("Content-Disposition: inline; filename='$name.pdf'");
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        ini_set('zlib.output_compression','0');
        die($str);
    }


    /**
     * There are some minimum requirements to generate a PDF.
     * For now, all we really need is a valid body (= HTML document)
     */
    private function validateOptions($options) {
        if(empty($options['body'])) {
            throw new Exception('No body specified');
        }
    }


    /**
     * We store the raw HTML, given in $options in some temporary html files
     */
    private function createTemporaryHtmlFiles($options) {

        $filename = $this->getTmpFilename($options['name']);

        $body = $this->createTmpHtmlFile($options, 'body', $filename);
        $cover = empty($options['cover']) ? null : $this->createTmpHtmlFile($options, 'cover', $filename);
        $header = empty($options['header']) ? null : $this->createTmpHtmlFile($options, 'header', $filename);
        $footer = empty($options['footer']) ? null : $this->createTmpHtmlFile($options, 'footer', $filename);

        $relativepdf = $this->getRelativepdf($filename);
        $pdf =  dirname(__FILE__)  . $relativepdf;
        $pdf_shell = escapeshellarg($pdf);

        $parms = array('body' => $body,
                       'cover' => $cover,
                       'header' => $header,
                       'footer' => $footer,

                       'pdf' => $pdf,
                       'pdf_shell' => $pdf_shell,
                       'relativepdf' => $relativepdf
            );

        return $parms;
    }


    /**
     * Given the $locations of our tmp html files, build the wkhtmltopdf bash statement
     * This statement includes options for cover, headers and footers
     */
    private function buildStatement($locations) {
        $statement = "wkhtmltopdf ";

        //headers, footers and cover page
        $statement .= empty($locations['cover']) ? '' : 'cover ' . $locations['cover'] . ' ';
        $statement .= empty($locations['header']) ? '' : "--header-html " . $locations['header'] . ' ';
        $statement .= empty($locations['footer']) ? '' : "--footer-html " . $locations['footer'] . ' ';

        //the actual body of the pdf
        $statement .= ( $locations['body'] . ' ' . $locations['pdf_shell'] );
        return $statement;
    }

    private function getRelativepdf($filename) {

        //create a unique PDF filename and path
        $pdf = "/tmp/$filename.pdf";
        return $pdf;
    }

    private function getTmpFilename($name) {
        $timestamp = @date('Y-m-d_H-m-s__') . $name . '__' . microtime(true);
        return "" . $timestamp;
    }


    /**
     * Creates a temporary html file
     * @param  array  $options  contains all our html strings
     * @param  [type] $property the $options key pointing to the html string we currently want to create a tmp location for
     * @param  [type] $filename base filename
     */
    private function createTmpHtmlFile(array $options, $property, $filename) { //$html, $filename

        $html = $options[$property];
        $filename = $filename . '_' . $property;
        $tmpFilePath = 'file://' . dirname(__FILE__)  . "/tmp/" . $filename . '.html';

        file_put_contents($tmpFilePath, $html);
        $tmpFilePath = escapeshellarg($tmpFilePath);

        return $tmpFilePath;
    }

    /**
     * Clear the TMP folder
     */
    private function clearTmpFolder() {
        $tmpFilePath = dirname(__FILE__)  . '/tmp/';
        $this->removeOldFiles($tmpFilePath, $this->maxTmpFileAge);
    }


    /**
     * http://stackoverflow.com/a/8965853/237739
     */
    private function removeOldFiles($directory, $maxAge)
    {
        $files = glob($directory."*");
        foreach($files as $file) {
            if(is_file($file)
            && time() - filemtime($file) >= $maxAge) {
                unlink($file);
            }
        }
    }
}
