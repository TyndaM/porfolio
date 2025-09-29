
<?php

    class PHP_Email_Form{
        public $to = '';
        public $from_name = '';
        public $from_email = '';
        public $subject = '';
        public $ajax = false;
        public $message = [];

        // Ajouter un message au mail
        public function add_message($content, $label ='', $priority = 0){
            $this->message[] = [
                'content' => $content,
                'label'=> $label,
                'priority'=> $priority
            ];
        }

        // Envoyer l'email
        public function send(){
            $email_text = '';
            foreach($this->message as $msg){
                $email_text .= ($msg['label'] ? $msg['label'].":":"") . $msg['content'] . "\n";
            }

            $headers = "From: {$this->from_name} < {$this->from_email}>";
            if(mail($this->to, $this->subject, $email_text, $headers)){
                return $this->ajax ? 'OK' : true;
            }
            else{
                return $this->ajax ? 'ERROR' : false;
            }
        }
    }
?>