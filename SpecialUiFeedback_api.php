<?php
class SpecialUiFeedback_api extends SpecialPage {

    function __construct() {
        parent::__construct('UiFeedback_api'/*name*/, ''/*restriction*/, false/*listed on Special:Specialpages*/);
    }

    function execute( $par ) {
        $request = $this->getRequest();
        $output = $this->getOutput();
        $this->setHeaders();

        $user = $this->getContext()->getUser();

        /* Rights to read and write */
        $can_read = $user->isAllowed( 'read_uifeedback' );
        $can_write = $user->isAllowed( 'write_uifeedback' );

        $output_text = 'nothing to see here, please move along';

        if(!$can_read){
            $output_text = 'i\'m sorry dave i can\'t let you do that';
        }else{
            /*______________________________   _________                                         .__            __
             /  _____/\_   _____/\__    ___/  /   _____/ ___________   ____   ____   ____   _____|  |__   _____/  |_
            /   \  ___ |    __)_   |    |     \_____  \_/ ___\_  __ \_/ __ \_/ __ \ /    \ /  ___/  |  \ /  _ \   __\
            \    \_\  \|        \  |    |     /        \  \___|  | \/\  ___/\  ___/|   |  \\___ \|   Y  (  <_> )  |
             \______  /_______  /  |____|    /_______  /\___  >__|    \___  >\___  >___|  /____  >___|  /\____/|__|
                    \/        \/                     \/     \/            \/     \/     \/     \/     \/             */
            if ($request->getVal('getScreenshotByID') != NULL) { /* an image is requested */
                $this->getOutput()->disable();
                $dbr = wfGetDB(DB_SLAVE);
                $res = $dbr->select(
                    'uifeedback', /*table*/
                    array('id', 'type', 'screenshot', 'image_size'), /*columns*/
                    'id = ' . $request->getVal('getScreenshotByID'), /*restriction*/
                    __METHOD__, /*?*/
                    array() /*options*/
                );
                foreach ($res as $row) { // Result is only one line
                    $contenttype = "image/png";
                    $contentsize = 0;
                    if ($row->type == "1") {
                        $contentsize = $row->image_size;
                        $contenttype = 'image/png';
                    }else{ // only Screenshot-Feedback has a Screenshot ;)
                        header('HTTP/1.0 404 Not Found');
                        echo "<h1>404 Not Found</h1>";
                        echo "The page that you have requested could not be found.";
                        exit();
                    }
                    header('Content-Type: ' . $contenttype);
//                    header('Content-Length: '.$contentsize);
                    echo $row->screenshot;
                    exit();
                }
            }else{
                if ($request->getMethod() == "POST") {
                    $this->getOutput()->disable();

                    function sizeofvar($var) {
                        $start_memory = memory_get_usage();
                        $var = unserialize(serialize($var));
                        return memory_get_usage() - $start_memory - PHP_INT_SIZE * 8;
                    }

                    $method = $request->getVal('method','feedback');

                    if($method === 'review'){
                        /*  __________            .__
                            \______   \ _______  _|__| ______  _  __
                             |       _// __ \  \/ /  |/ __ \ \/ \/ /
                             |    |   \  ___/\   /|  \  ___/\     /
                             |____|_  /\___  >\_/ |__|\___  >\/\_/
                                    \/     \/             \/        */
                        /* Review a Feedback-Item */
                        $this->getOutput()->disable();
                        $id         = $request->getInt('id'      ,-1);
                        $new_status = $request->getInt('status'  ,-1);
                        $comment    = $request->getVal('comment' ,'none');
                        $reviewer   = $user->getName();
                        if($id != -1 && $new_status != -1){
                            $dbw    = wfGetDB(DB_MASTER);
                            $dbw->begin();
                            $values = array('status' => $new_status, 'comment' => $comment);
                            $conds  = array('id' => $id);
                            $dbw    = wfGetDB(DB_MASTER);
                            $dbw -> update( 'uifeedback', $values, $conds, __METHOD__, array() );
                            $values = array('feedback_id' => $id,
                                'reviewer'    => $reviewer,
                                'status'      => $new_status,
                                'comment'     => $comment
                            );
                            echo $dbw -> insert( 'uifeedback_reviews', $values, __METHOD__, array() );
                            $dbw->commit();
                            echo "<br/>done:<br/>";
                        }
                        echo "feedback_id: $id<br/>status: $new_status<br/>comment: $comment<br/>reviewer: $reviewer";
                        exit();
                        /*end Review*/
                    }else if($method === 'count'){
                        /*  _________                      __
                            \_   ___ \  ____  __ __  _____/  |_
                            /    \  \/ /  _ \|  |  \/    \   __\
                            \     \___(  <_> )  |  /   |  \  |
                             \______  /\____/|____/|___|  /__|
                                    \/                  \/      */
                        /* count display of Feedbackbutton and 'Request for Feedback'*/
                        $this->getOutput()->disable();

                        $type   = $request->getInt('type',  -1); /* 0 dynamic request (popup), 1 questionnaire-button, 2 screenshot-button */
                        $show   = $request->getInt('show',   0); /* 1 = true */
                        $click  = $request->getInt('click',  0); /* 1 = true*/
                        $sent   = $request->getInt('sent',   0); /* 1 = true*/

                        if((!$can_read) || ( $type < 0 || $type > 2) || ( $show !== 1 && $click !== 1 && $sent !== 1 ) ){
                            header('HTTP/1.0 400 Bad Request', true, 400);
                            echo 'i\'m sorry dave i can\'t let you do that';
                            echo '<br/>type:'.$type;
                            echo '<br/>show:'.$show;
                            echo '<br/>click:'.$click;
                            echo '<br/>sent:'.$sent;
                            exit();
                        }

                        /* if click, show and sent are 1 I have no idea what to do */
                        if( $click == $show && $show == $sent ){
                            header('HTTP/1.0 400 Bad Request', true, 400);
                            exit();
                        }

                        if($show){
                            $value = array('shown = shown + 1' );
                        }else if($click){
                            $value = array('clicked = clicked + 1' );
                        }else if($sent){
                            $value = array('sent = sent + 1' );
                        }else{
                            header('HTTP/1.0 400 Bad Request', true, 400);
                            exit();
                        }

                        /* update table */
                        $dbw = wfGetDB(DB_MASTER);
                        $dbw->update(   'uifeedback_stats',
                                        $value,
                                        array( 'type' => $type ),
                                        __METHOD__
                                    );
                        exit();
                    }else{
                        /*  ___________               .______.                  __
                            \_   _____/___   ____   __| _/\_ |__ _____    ____ |  | __
                             |    __)/ __ \_/ __ \ / __ |  | __ \\__  \ _/ ___\|  |/ /
                             |     \\  ___/\  ___// /_/ |  | \_\ \/ __ \\  \___|    <
                             \___  / \___  >\___  >____ |  |___  (____  /\___  >__|_ \
                                 \/      \/     \/     \/      \/     \/     \/     \/*/
                        $file_content = '';
                        $file_size    = 0;
                        $feedbacktype = 1; /* 1 questionnaire-button, 2 screenshot-button */
                        if ($_POST['ui-feedback-type'] == 1) { // screenshot
                            $feedbacktype = 2;
//                            echo "screenshot sent<br/>";
                            $uploadName = 'screenshot';
                            if (array_key_exists($uploadName, $_FILES)) { // Upload via files
                                // TODO ?
                                $file_content = file_get_contents($request->getFileTempname($uploadName));
                            } elseif (array_key_exists($uploadName, $_POST)) { // Upload via dataURI
                                $file_content = substr($_POST[$uploadName], strpos($_POST[$uploadName], ",") + 1);
                                $file_content = base64_decode($file_content);
                            }
                            $file_size = sizeofvar($file_content);
                            echo "screenshot sent<br/>\n";
                        }

                        $anonymous =  $request->getVal('ui-feedback-anonymous') == 'true';
                        if($anonymous){
                            $username = '';
                        }else{
                            /* username or IP */
                            $username = $request->getVal('ui-feedback-username');
                            if ($username == NULL || $username == 'null' || $username == '') {
                                $username = $_SERVER['REMOTE_ADDR'];
                            }
                        }
                        echo "username<br/>\n";


                        $notify = 0;
                        if(!$anonymous){
                            $notify = $request -> getVal( 'ui-feedback-notify') == 'true';
                        }
                        echo "notify<br/>\n";


                        $task = $request->getVal('ui-feedback-task');
                        $other = $request->getVal('ui-feedback-task-other');
                        if(!is_null($other) && $other !== 'undefined')
                            $task .= ' - '.$other;
                        echo "task<br/>\n";

                        $done = $request -> getVal( 'ui-feedback-done' );
                        if($done === 'undefined')
                            $done = null;
                        echo 'done: '+$done+'<br/>\n';


                        $type = $request -> getInt( 'ui-feedback-type');
                        if($type !== 1 && $type !== 0){
                            header('HTTP/1.0 400 Bad Request', true, 400);
                            echo 'i\'m sorry dave i can\'t let you do that';
                            exit();
                        }

                        $url = $request -> getVal( 'ui-feedback-url' );

                        $a = array(
                            'type'       => $type,
                            'url'        => $url,
                            'task'       => $task,
                            'done'       => $done,
                            'importance' => $request -> getInt( 'ui-feedback-importance', 0 ),
                            'happened'   => $request -> getInt( 'ui-feedback-happened', 0 ),
                            'text1'      => $request -> getVal( 'ui-feedback-text1', '' ),
//                            'text2'      => $request -> getVal( 'ui-feedback-text2', '' ),
//                            'text3'      => $request -> getVal( 'ui-feedback-text3', '' ),
//                            'text4'      => $request -> getVal( 'ui-feedback-text4', '' ),
//                            'text5'      => $request -> getVal( 'ui-feedback-text5', '' ),
                            'username'   => $username,
                            'useragent'  => $request -> getVal( 'ui-feedback-useragent' ),
                            'notify'     => $notify,
                            'image_size' => $file_size,
                            'screenshot' => $file_content,
                            'status'     => '0',
                            'comment'    => ''
                        );

                        $dbw = wfGetDB( DB_MASTER );
                        /* insert Feedback into Database */
                        $dbw -> begin();
                        $dbw -> insert( 'uifeedback', $a, __METHOD__, array() );
                        $id = $dbw->insertId();
                        echo 'new id:'+$id+'</br>\n';
                        /* update stats */
                        /* $feedbacktype: 1 questionnaire-button, 2 screenshot-button */
                        $dbw -> update( 'uifeedback_stats', array( 'sent = sent + 1' ), array( 'type' => $feedbacktype ), __METHOD__ );
                        echo 'feedbacktype:'+$feedbacktype;
                        $dbw -> commit();
                    } /* end Feedback */
                }/*end POST*/
            }
        }
        /* write to output */
        $output->addHTML( $output_text );
    }


    function addStatusLinks($status){

    }

}

