
##Installation
for ease of development, a wkhtmltopdf copy can be found in the root of this project.However, you should copy this one to /usr/bin (for global use).

Wkhtmltopdf should also be installed on the server (into /usr/bin), but the binary will be different: get the latest one from the [official site](http://wkhtmltopdf.org/).




##Structure
This demo is built with wkthmltopdf 12.1 on mac OSX (patched QT)

##Usage
For now, just check out the demo. This should be properly documented in the near future.

##Gotcha's
- This pdflib will only work on unix environments.
- It is [not possible](https://github.com/wkhtmltopdf/wkhtmltopdf/issues/1676) to generate a pdf with headers and footer AND a cover. Should get fixed in [12.2](https://github.com/wkhtmltopdf/wkhtmltopdf/blob/6a13a51/CHANGELOG.md)


