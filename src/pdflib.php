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


    public function generate(array $options) {

        $this->clearTmpFolder();
        $this->validateOptions($options);

        $parms = $this->createLocationParameters($options);
        $statement = $this->buildStatement($parms);

        //actually execute the statement
        $output = shell_exec($statement);

        if(!empty($options['immediateDownload'])) {
            $this->pushPdfDownload($statement, $parms);
        } else {
            return $parms['relativePdfLocation'];
        }
    }

    private function pushPdfDownload($statement, $parms) {

        $pdfLocation = $parms['pdfLocation'];

        //grab the contents of the statement and send it to the user
        $str = file_get_contents($pdfLocation);
        header('Content-Type: application/pdf');
        header('Content-Length: '.strlen($str));
        header('Content-Disposition: inline; filename="pdf.pdf"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        ini_set('zlib.output_compression','0');
        die($str);
    }

    private function validateOptions($options) {
        if(empty($options['body'])) {
            throw new Exception('No body specified');
        }
    }

    private function createLocationParameters($options) {
        $bodyLocation = $this->getTmpLocation($options['body']);
        $coverLocation = empty($options['cover']) ? null : $this->getTmpLocation($options['cover']);
        $headerLocation = empty($options['header']) ? null : $this->getTmpLocation($options['header']);
        $footerLocation = empty($options['footer']) ? null : $this->getTmpLocation($options['footer']);

        $relativePdfLocation = $this->getRelativePdfLocation();
        $pdfLocation =  dirname(__FILE__)  . $relativePdfLocation;
        $pdfLocation_shell = escapeshellarg($pdfLocation);

        $parms = array('bodyLocation' => $bodyLocation,
                       'coverLocation' => $coverLocation,
                       'headerLocation' => $headerLocation,
                       'footerLocation' => $footerLocation,
                       'pdfLocation' => $pdfLocation,
                       'relativePdfLocation' => $relativePdfLocation
            );

        return $parms;
    }

    private function buildStatement($options) {
        $pdfLocation_shell = escapeshellarg($options['pdfLocation']);

        $statement = "wkhtmltopdf ";

        //headers, footers and cover page
        $statement .= empty($options['coverLocation']) ? '' : 'cover ' . $options['coverLocation'] . ' ';
        $statement .= empty($options['headerLocation']) ? '' : "--header-html " . $options['headerLocation'] . ' ';
        $statement .= empty($options['footerLocation']) ? '' : "--footer-html " . $options['footerLocation'] . ' ';

        //the actual body of the pdf
        $statement .= ( $options['bodyLocation'] . ' ' . $pdfLocation_shell );

        return $statement;
    }

    private function getRelativePdfLocation() {
        //create a unique PDF filename and path
        $time = microtime();
        $pdfLocation = "/tmp/tmp_pdf-$time.pdf";
        return $pdfLocation;
    }



    private function getTmpLocation($html) {
        $tmpFilePath = dirname(__FILE__)  . "/tmp/";

        //create a new tmp file that has the contents of $html
        $tmpFile = tempnam($tmpFilePath,'tmp_WkHtmlToPdf_');
        file_put_contents($tmpFile, $html);
        rename($tmpFile, ($tmpFile.='.html'));

        $tmpFilePath = escapeshellarg('file://' . $tmpFile);

        return $tmpFilePath;
    }

    private function clearTmpFolder() {
        //Clear the tmp folder
        $tmpFilePath = dirname(__FILE__)  . '/tmp/';
        $this->removeOldFiles($tmpFilePath, $this->maxTmpFileAge);
    }

    private function removeOldFiles($directory, $maxAge)
    {
        //http://stackoverflow.com/a/8965853/237739
        $files = glob($directory."*");
        foreach($files as $file) {
            if(is_file($file)
            && time() - filemtime($file) >= $maxAge) {
                unlink($file);
            }
        }
    }
}
