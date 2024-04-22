<?php

declare(strict_types=1);

namespace App\Service;

class InvoiceGetter
{
    public function __construct(private string $hostname, private string $username, private string $password)
    {
    }

    public function getInvoices(): array
    {
        $inbox = imap_open($this->hostname, $this->username, $this->password) or die('Cannot connect to your mail: ' . imap_last_error());
        $emails = imap_search($inbox,'ALL');

        $return = [];

        if($emails) {
            rsort($emails);
            $i=0;
            foreach($emails as $email_number) {
                $return[$i]['infos'] = $this->getInfoFromEmail($inbox, $email_number);
                $return[$i]['attachments'] = $this->getAttachmentFromEmail($inbox, $email_number);
                $i++;
            }
        }

        return $return;
    }

    public function getInfoFromEmail($inbox, $email_number): array
    {
        $overview = imap_fetch_overview($inbox, (string)$email_number,0);
        $message = imap_fetchbody($inbox,$email_number,"1");

        return [
            'from' => $overview[0]->from,
            'subject' => $overview[0]->subject,
            'message' => $message
        ];
    }

    public function getAttachmentFromEmail($inbox, $email_number): array
    {
        $structure = imap_fetchstructure($inbox, $email_number);
        if(isset($structure->parts) && count($structure->parts)) {
            $attachments = [];
            for($i = 0; $i < count($structure->parts); $i++) {
                if($structure->parts[$i]->ifdparameters) {
                    foreach($structure->parts[$i]->dparameters as $object) {
                        if(strtolower($object->attribute) == 'filename') {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                            $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, (string) ($i+1));

                            // check if this is base64 encoding
                            if($structure->parts[$i]->encoding == 3) {
                                $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                            }

                            $file = fopen(uniqid().$attachments[$i]['filename'], "w+");
                            fwrite($file, $attachments[$i]['attachment']);
                            fclose($file);

                        }
                    }
                }
            }
            return $attachments;
        } else {
            return [];
        }
    }
}