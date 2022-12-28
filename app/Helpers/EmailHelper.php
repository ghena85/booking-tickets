<?php

namespace App\Helpers;

use App\Repositories\VarRepository;
use View;

class EmailHelper
{

    /**
     * @param Media|null $media
     *
     * @return string
     */
    public static function send($to, $data, $name = null, $cc = null, $subject = null,$mailView = 'tickets',$fileAttachemnt = '',$info = [])
    {
        $message = View::make('mails.'.$mailView)->with(['data' => $data,'info' => $info])->render();
        $reservedStr = (new VarRepository())->getVar('email_reserved_tickets_for');
        $subject = $subject ?? config('app.name') . ' - ' . $reservedStr . ' - ' . $name;

        $headers = 'From: booking@booking.md' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Content-type:text/html;charset=UTF-8' . "\r\n" .
            'Cc: '.$cc . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        if($fileAttachemnt)
        {
            $body        = "<p>The PDF is attached.</p>"; // email body

            $from        = "booking@booking.md"; // address message is sent from

            $pdfLocation = $fileAttachemnt; // file location
            $pdfName     = "tickets.pdf"; // pdf file name recipient will get
            $filetype    = "application/pdf"; // type

            // create headers and mime boundry
            $eol = PHP_EOL;
            $semi_rand     = md5(time());
            $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
            $headers       = "From: $from$eol" .
                "MIME-Version: 1.0$eol" .
                "Content-Type: multipart/mixed;$eol" .
                " boundary=\"$mime_boundary\"";

            // add html message body
            $message = "--$mime_boundary$eol" .
                "Content-Type: text/html; charset=\"iso-8859-1\"$eol" .
                "Content-Transfer-Encoding: 7bit$eol$eol" .
                $body . $eol;

            // fetch pdf
            $file = fopen($pdfLocation, 'rb');
            $data = fread($file, filesize($pdfLocation));
            fclose($file);
            $pdf = chunk_split(base64_encode($data));

            // attach pdf to email
            $message .= "--$mime_boundary$eol" .
                "Content-Type: $filetype;$eol" .
                " name=\"$pdfName\"$eol" .
                "Content-Disposition: attachment;$eol" .
                " filename=\"$pdfName\"$eol" .
                "Content-Transfer-Encoding: base64$eol$eol" .
                $pdf . $eol .
                "--$mime_boundary--";


        }
        mail($to, $subject, $message, $headers);
    }

}
