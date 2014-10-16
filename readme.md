
##Installation
for ease of development, a wkhtmltopdf copy can be found in the root of this project. However, you should copy this one to /usr/bin (for global use).
<sub>Note: this is the Mac OSX 32bit 12.1 binary</sub>

Wkhtmltopdf should also be installed on the server (into /usr/bin), but the binary will be different: get the latest one from the [official site](http://wkhtmltopdf.org/).




##Structure
This demo is built with wkthmltopdf 12.1 on mac OSX (patched QT)

##Usage
For now, just check out the demo. This should be properly documented in the near future.

##Gotcha's
- This pdflib will only work on unix environments.
- It is [not possible](https://github.com/wkhtmltopdf/wkhtmltopdf/issues/1676) to generate a pdf with headers and footer AND a cover. Should get fixed in [12.2](https://github.com/wkhtmltopdf/wkhtmltopdf/blob/6a13a51/CHANGELOG.md)


###Good-to-knows and catches
##cndjs-issue
Make it have an http:// in front of the cdn:
<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css"/>,
so no:
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css"/>


##print css
Wkhtmltopdf seems to ingore media=print in the stylesheet links. It takes the screen though.
Add it with javascript after you click the download button and before you generate the pdf.

eg.:
<script>
    $('.download-pdf').click(function(e){
        e.preventDefault();

        $("head link[rel='stylesheet']").last().after("<link rel='stylesheet' href='http://"+window.location.hostname+"/assets/css/pdf.css' type='text/css' media='screen'>");

        $.post("php/savetopdf.php", {html: $('html')[0].outerHTML}, function(response){

        });

    });
</script>
