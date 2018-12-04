<?php
###############################################################################
#
#  phpMailMerge v1.0.5 | (c) 2018 Ralf Bartsch (@r84r)
#
#  license & info: https://github.com/r84r/phpMailMerge
#
###############################################################################
#
#  Sends a serial letter in multipart MIME format to all tagged entries of a 
#  CSV database. Depending on the language, a German and English version will 
#  be generated. In addition to the HTML message, the email also contains an 
#  alternative plain text message. If attachments are defined, they will be 
#  added.
#
###############################################################################
#
#  phpMailMerge.send.php | execution script
#
#  revisions:
#    2018-09-04, Ralf Bartsch
#    - mod: prepared for GitHub: comments and script messages set in english
#    2018-06-13, Ralf Bartsch
#    - new: added default language (if language selector doesn't match)
#    - new: use company name as email name if first name is empty
#    2017-05-04, Ralf Bartsch
#    - new: added additional language selectors "de" and "en"
#    2017-02-08, Ralf Bartsch
#    - new: added sleep() and flush() at the end of the loop
#    2016-12-20, Ralf Bartsch
#    - new: added inline images
#    - mod: renamed some variables
#    - fix: correct encoding
#
#  initial release: 
#    2016-10-10, Ralf Bartsch
#    - for the GKT symposium (www.gleitketten.de)
#
###############################################################################

    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    # basic setup and functions
    $eol = "\r\n";
    function encodeString($string) {
        $mimePrefs = array ("scheme" => "Q",
                            "input-charset" => "utf-8",
                            "output-charset" => "utf-8",
                            "line-length" => 76,
                            "line-break-chars" => $eol);
        return preg_replace("/^:\s+/", "", iconv_mime_encode("", $string, $mimePrefs));
    }

    # generate seperators for the different parts of the email
    $mimeBoundary     = "-----=" . md5(uniqid(mt_rand(), 1));
    $mimeBoundary_alt = "-----=alt" . md5(uniqid(mt_rand(), 1));
    $mimeBoundary_rel = "-----=rel" . md5(uniqid(mt_rand(), 1));

    # load setup (abort script if an error occur)
    require("phpMailMerge.setup.php");
    $message_de = file_get_contents($message_de);
    $message_en = file_get_contents($message_en);
    if ($message_de === false or $message_en === FALSE) {
        exit("At least one HTML message can not be found.");
    }

    # sender
    $from = $fromMail;
    if ($fromMail !== "") {
        if ($fromName !== "") {
            $from = encodeString($fromName)." <".$fromMail.">";
        }
    }  
    # reply
    $replyTo = $replyMail;
    if ($replyMail !== "") {
        if ($replyName !== "") {
            $replyTo = encodeString($replyName)." <".$replyMail.">";
        }
    }
    if ($replyTo == "") {
        $replyTo   = $from;
        $replyName = $fromName;
        $replyMail = $fromMail;
    }

    # import german attachments
    if ($attachments_de !== "") {
        $files = explode(";", $attachments_de);
        $attachments_de = array();
        foreach ($files as $file) {
            if (file_exists($file) === FALSE) { exit("Attachment \"".basename($file)."\" not found."); }
            $name = basename($file);
            $size = filesize($file);
            $data = chunk_split(base64_encode(file_get_contents($file)));
            $type = mime_content_type($file);
            $attachments_de[] = array("name"=>$name, "size"=>$size, "type"=>$type, "data"=>$data);
        }
    }

    # import english attachments
    if ($attachments_en !== "") {
        $files = explode(";", $attachments_en);
        $attachments_en = array();
        foreach ($files as $file) { 
            if (file_exists($file) === FALSE) { exit("Attachment \"".basename($file)."\" not found."); }
            $name = basename($file);
            $size = filesize($file);
            $data = chunk_split(base64_encode(file_get_contents($file)));
            $type = mime_content_type($file);
            $attachments_en[] = array("name"=>$name, "size"=>$size, "type"=>$type, "data"=>$data);
        }
    }

    # import german images
    if ($inlineImages_de !== "") {
        $files = explode(";", $inlineImages_de);
        $images_de = array();
        foreach ($files as $file) {
            if (file_exists($file) === FALSE) { exit("Image \"".basename($file)."\" not found."); }
            $name = basename($file);
            $size = filesize($file);
            $data = chunk_split(base64_encode(file_get_contents($file)));
            $type = mime_content_type($file);
            $images_de[] = array("name"=>$name, "size"=>$size, "type"=>$type, "data"=>$data);
        }
    }

    # import english images
    if ($inlineImages_en !== "") {
        $files = explode(";", $inlineImages_en);
        $images_en = array();
        foreach ($files as $file) {
            if (file_exists($file) === FALSE) { exit("Image \"".basename($file)."\" not found."); }
            $name = basename($file);
            $size = filesize($file);
            $data = chunk_split(base64_encode(file_get_contents($file)));
            $type = mime_content_type($file);
            $images_en[] = array("name"=>$name, "size"=>$size, "type"=>$type, "data"=>$data);
        }
    }

    # create plain text version
        # 1) remove line breaks
        # 2) delete complete <head>
        # 3) convert HTML breaks to line breaks
        # 4) remove all HTML tags
        # 5) convert HTML special chars to normal chars
        # 6) replace special chars
    # german
    $message_plain_de = preg_replace("/\r|\n/", "", $message_de); #1
    $message_plain_de = preg_replace("%<head>.*</head>%", "", $message_plain_de); #2
    $message_plain_de = preg_replace("/\<br(\s*)?\/?\>/i", $eol, $message_plain_de); #3
    $message_plain_de = strip_tags($message_plain_de); #4
    $message_plain_de = htmlspecialchars_decode($message_plain_de); #5
    $message_plain_de = str_replace("&bull;", "-", $message_plain_de); #6
    # english
    $message_plain_en = preg_replace("/\r|\n/", "", $message_en); #1
    $message_plain_en = preg_replace("%<head>.*</head>%", "", $message_plain_en); #2
    $message_plain_en = preg_replace("/\<br(\s*)?\/?\>/i", $eol, $message_plain_en); #3
    $message_plain_en = strip_tags($message_plain_en); #4
    $message_plain_en = htmlspecialchars_decode($message_plain_en); #5
    $message_plain_en = str_replace("&bull;", "-", $message_plain_en); #6

    # create HTML version
    $message_html_de = $message_de;
    $message_html_en = $message_en;

    # open log file for append new log ########################################
    $log = fopen($logFile, "a");
    fputs($log, $eol);
    fputs($log, date("Y-m-d H:i:s").$eol);

    ###########################################################################
    # open datenbase, run through and send an email to each entry #############
    ###########################################################################
    $row = 0;
    if (($handle = fopen($databaseFile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, $maxReadLength, $delimiter)) !== FALSE) {
            $row++;

            # insert element at beginning (to beginn with counter 1)
            array_splice($data, 0, 0, "");

            # identify language and pass proper blocks
			# if necessary don't consider email in mailing letter
            $language = strtolower($data[$databaseColumnLanguage]);
            if ($defaultLanguage != "") {
                if ((($language == "d") or ($language == "de") or
                   ($language == "e") or ($language == "en")) == false) {
                   $language = $defaultLanguage;
                }
            }
            if (($language == "d") or ($language == "de")) {
                $subject       = $subject_de;
                $message_plain = $message_plain_de;
                $message_html  = $message_html_de;
                $attachments   = $attachments_de;
                $images        = $images_de;
            } elseif (($language == "e") or ($language == "en")) {
                $subject       = $subject_en;
                $message_plain = $message_plain_en;
                $message_html  = $message_html_en;
                $attachments   = $attachments_en;
                $images        = $images_en;
            } else {
                continue;
            }

            # generate salutation
            $toMail     = $data[$databaseColumnEmail];
            $salutation = $data[$databaseColumnTitle];
            $company    = $data[$databaseColumnCompany];
            if ($data[$databaseColumnName] == "") {
                $toName = $company;
            } else {
                if ($databaseColumnFirstName == 0) {
                    $toName  = $company;
                } else {
                    $toName  = $data[$databaseColumnFirstName]." ".$data[$databaseColumnName];
                }
                $salutation .= ($data[$databaseColumnAcTitle] !== "") ? (" ".$data[$databaseColumnAcTitle]) : ("");
                $salutation .= " ".$data[$databaseColumnName];
            }
            # replace placeholders
            $message_plain = str_replace("%SUBJECT%",    $subject,    $message_plain);
            $message_plain = str_replace("%SALUTATION%", $salutation, $message_plain);
            $message_html  = str_replace("%SUBJECT%",    $subject,    $message_html);
            $message_html  = str_replace("%SALUTATION%", $salutation, $message_html);
            
            # generate recipient
            $to = $toMail;
            if ($toName !== "") {
                $to = encodeString($toName)." <".$toMail.">";
            }
            
            ###################################################################
            #
            #  multipart/mixed
            #    +- multipart/alternative
            #    |    +- text/plain
            #    |    +- multipart/relative
            #    |         +- text/html
            #    |         +- image/jpeg (inline)
            #    +- image/jpeg (attached)
            #
            #  > without inline elements "multipart/relative" gets "text/html"
            #  > without attached images the structure begins with 
            #    "multipart/alternative"
            #
            ###################################################################
            # create multipart/related or rather text/html
            $html = array();
            if (!is_array($images)) {
                $html[] = "Content-Type: text/html; charset=utf-8";
                $html[] = "Content-Transfer-Encoding: 7bit";
                $html[] = "";
                $html[] = $message_html;
            } else {
                $html[] = "Content-Type: multipart/related;";
                $html[] = "\tboundary=\"$mimeBoundary_rel\"";
                $html[] = "";
                $html[] = "--$mimeBoundary_rel";
                $html[] = "Content-Type: text/html; charset=utf-8";
                $html[] = "Content-Transfer-Encoding: 7bit";
                $html[] = "";
                $html[] = $message_html;
                $html[] = "";
                $count  = 0;
                foreach($images as $image) {
                    $count++;
                    $html[] = "--$mimeBoundary_rel";
                    $html[] = "Content-Type: ".$image['type'].";";
                    $html[] = "\tname=\"".$image['name']."\";";
                    $html[] = "Content-Disposition: inline;";
                    $html[] = "\tfilename=\"".$image['name']."\";";
                    $html[] = "Content-Transfer-Encoding: base64";
                    $html[] = "Content-ID: <img".$count.">";
                    $html[] = "";
                    $html[] = $image['data'];
                }
                $html[] = "--$mimeBoundary_rel--"; 
            }

            ###################################################################
            # create header ###################################################
            $headers   = array();
            $headers[] = "From: $from";
            $headers[] = "Reply-To: $replyTo";
            $headers[] = "X-Mailer: PHP/".phpversion();
            $headers[] = "MIME-Version: 1.0";
            if (is_array($attachments)) {
                $headers[] = "Content-type: multipart/mixed;";
            } else {
                $headers[] = "Content-type: multipart/alternative;";
                # $mimeBoundary_alt überschreiben/gleichsetzen
                $mimeBoundary_alt = $mimeBoundary;
            }
            $headers[] = "\tboundary=\"".$mimeBoundary."\"";

            ###################################################################
            # create body/message #############################################
            $body   = array();
            $body[] = "This is a multi-part message in MIME format.";
            $body[] = "";
            if (is_array($attachments)) {
                $body[] = "--$mimeBoundary";
                $body[] = "Content-Type: multipart/alternative;";
                $body[] = "\tboundary=\"$mimeBoundary_alt\"";
                $body[] = "";
            }
            $body[] = "--$mimeBoundary_alt";
            $body[] = "Content-Type: text/plain; charset=utf-8";
            $body[] = "Content-Transfer-Encoding: 7bit";
            $body[] = "";
            $body[] = $message_plain;
            $body[] = "";
            $body[] = "--$mimeBoundary_alt";
            $body   = array_merge(array_values($body), array_values($html));
            $body[] = "";
            $body[] = "--$mimeBoundary_alt--";
            if (is_array($attachments)) {
                foreach($attachments as $attachment) {
                    $body[] = "";
                    $body[] = "--$mimeBoundary";
                    $body[] = "Content-Disposition: attachment;";
                    $body[] = "\tfilename=\"".$attachment['name']."\";";
                    $body[] = "Content-Length: .".$attachment['size'].";";
                    $body[] = "Content-Type: ".$attachment['type'].";";
                    $body[] = "\tname=\"".$attachment['name']."\"";
                    $body[] = "Content-Transfer-Encoding: base64";
                    $body[] = "";
                    $body[] = $attachment['data'];
                }
                $body[] = "--$mimeBoundary--"; 
            }

            ###################################################################
            # send email and log it in browser and file #######################
            if ($data[$databaseColumnName] == "") {
                $info = "Company " . $company . "\t" . $toMail;
            } else {
                if ($company == "") {
                    $info = $toName . "\t" . $toMail;
                } else {
                    $info = $toName . " (" . $company . ")\t" . $toMail;
                }
            }
            if (mail($to,
                     encodeString($subject),
                     implode($eol, $body),
                     implode($eol, $headers),
                     "-f $fromMail")) {
                $report = ($row-1) . "\tSent:\t" .$info;
            } else {
                $report = ($row-1) . "\tFail:\t" .$info;
            }
            echo $report."<br>".$eol;
            fputs($log, $report.$eol);
			
            # clear output puffer (for instant browser output)
            flush();
            ob_flush();
            # wait a short time (can be removed if mailer is fast)
            sleep(0.1);
        }
        # close datenbase
        fclose($handle);
    }
    
    # close log file
    fclose($log);
?>
