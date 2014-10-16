<?php

use BitsOfLove\PdfLib;
require_once('../src/pdflib.php');

$demo = new Demo();
$demo->generatePdf();

class Demo {

    public function generatePdf() {

        //1. Create a new PdfLib class. libOptions are optional
        $libOptions = $this->getPdfLibOptions();
        $pdfLib = new PdfLib($libOptions);

        //2. Generate a PDF via raw HTML options (body, header, footer, cover)
        $pdfOptions = $this->getPdfOptions();
        $result = $pdfLib->generate($pdfOptions);

        //3. output result
        echo('<h1>Result: </h1>');
        var_dump($result);
    }

    /**
     * Returns the global PdfLib options.
     * In this example we always remove all previous tmp files (default = keep for 2 days)
     */
    private function getPdfLibOptions() {
        return array('maxTmpFileAge' => 0);
    }

    /**
     * Returns an array containing the raw HTML, used to generate our PDF.
     * Also some additional parameters, like action and name are provided.
     * Lastly, we tell the lib to copy the generated pdf to our given location.
     *     (it will respect the name property)
     */
    private function getPdfOptions() {
        //using a framework, you would probably create dynamic templates
        //this here is just a static example. in any case, we need the raw html + js to send to wkhtmltopdf

        $pdfOptions = array('body' => file_get_contents('templates/body.html'),
                            //'cover' => file_get_contents('templates/cover.html'),
                            'footer' => file_get_contents('templates/footer.html'),
                            'header' => file_get_contents('templates/header.html'),

                            'action' => PdfLib::$ACTION_RETURN_URL,
                            'name' => 'demo',
                            'copy' => getcwd() . '/pdf');
        return $pdfOptions;
    }
}
