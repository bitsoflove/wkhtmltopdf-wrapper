
##Installation
for ease of development, a wkhtmltopdf copy can be found in the root of this project
however, you should copy this one to /usr/bin (for global use)
wkhtmltopdf should also be installed on the server (into /usr/bin), but the binary will be different: get the latest one from the official site.


##Structure
This demo is built with wkthmltopdf 12.1 on mac OSX (patched QT)



##Statements

###With cover
> wkhtmltopdf cover cover.html --page-size A4 body.html test.pdf

###With headers and footers
Page numbering is done with javascript in the footer
> wkhtmltopdf --header-html header.html --footer-html footer.html --page-size A4 body.html test.pdf


###With headers and footers AND cover
> DOES NOT WORK - https://github.com/wkhtmltopdf/wkhtmltopdf/issues/1676
Should get fixed in [12.2](https://github.com/wkhtmltopdf/wkhtmltopdf/blob/6a13a51/CHANGELOG.md)
