<?php
###############################################################################
#
#  phpMailMerge v1.0.5 | (c) 2018 Ralf Bartsch (@r84r)
#
#  license & info: https://github.com/r84r/phpMailMerge
#
###############################################################################
#
#   Important: The CSV file must be UTF-8 encoded! Otherwise, umlauts are 
#              displayed incorrectly. UTF-8 is a subset of Unicode.
#              With Notepad++: Open CSV file > Encoding > Convert to UTF-8
#
#     Message: - should be written as HTML
#              - the script will always create a plain text version and send it
#              - MIME compliant emails should be no more than 80 characters 
#                per line. Therefore, it is appropriate to apply this in the 
#                message body. In HTML, a normal line break is not evaluated 
#                anyway: for this, "<br>" must be used.
#                (The comment and section delimiters are 80 characters long)
#              - the string "-----=" is a MIME control string and must not be 
#                used, otherwise it will cause erroneous mails
# Attachments: - must be in the script path or subpaths
#              - the path is relative
#              - several attachments have to be separated with semicolon
#      Images: - like attachments
#              - must be specified in the 'message' part with 
#                <img src="cid:imgX"> where X is a placeholder for the 
#                position in the image list starting with 1
#              - currently only JPEG is supported
#
###############################################################################
#
#  phpMailMerge.setup.php | setup script
#
#  last revision  : 2018-06-13, Ralf Bartsch
#
#  initial release: 2016-10-10, Ralf Bartsch, for the GKT symposium
#
###############################################################################


#==============================================================================
# database (csv file)
#==============================================================================

# file location (use relative path)
$databaseFile = "phpMailMerge.database.csv";
$logFile      = "phpMailMerge.log.txt";

# max. chars per row in csv file (speed relevant)
$maxReadLength = 512;

# delimiter in csv file
$delimiter = ";";

# column assignment
$databaseColumnCompany   =  2; # company
$databaseColumnTitle     =  3; # form of address
$databaseColumnAcTitle   =  4; # academic title
$databaseColumnFirstName =  5; # first name
$databaseColumnName      =  6; # family name
$databaseColumnEmail     = 11; # email address
$databaseColumnLanguage  = 12; # language

# default language value
# (use "" for not sending an email, if language doesn't match)
$defaultLanguage = "de";

#==============================================================================
# email
#==============================================================================

# sender
$fromName = "phpMailMerge Script";
$fromMail = "no@reply.org";

# reply to (leave it empty if the reply should go to the sender)
$replyName = "";
$replyMail = "";

# german (can be also another language)
$subject_de      = "Serienbriefe per PHP versenden";
$attachments_de  = "";
$inlineImages_de = "unsplash-sidelinejones-mailboxes.jpg";
$message_de      = "mail_template_de.html";

# englisch (can be also another language)
$subject_en      = "Send serial letters with PHP";
$attachments_en  = "";
$inlineImages_en = "unsplash-sidelinejones-mailboxes.jpg";
$message_en      = "mail_template_en.html";

?>
