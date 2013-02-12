<?php
class SpecialUiFeedback extends SpecialPage {

    function __construct() {
        parent::__construct('UiFeedback');
    }

    function execute( $par ) {
        $request = $this->getRequest();
        $output = $this->getOutput();
        $this->setHeaders();

        $user = $this->getContext()->getUser();

        /* Rights to read and write */
        $can_read = $user->isAllowed( 'read_uifeedback' );
        $can_write = $user->isAllowed( 'write_uifeedback' );

        $output_text = '';

        if(!$can_read){
            $output_text = 'You have not the right permissions to see that Page';
        }else{ /* can read */
            /* Arrays for Output */
            $importance_array = array('','--','-', '0', '+', '++');
            $happened_array = array('','did not work as expected','got confused', 'missing feature', 'other');
            $bool_array     = array('no', 'yes');
            $status_array   = array('open','in review','closed','declined');


            /* get Request-data */
            $id                 = $request->getInt('id'     ,-1);

            $filter_status      = $request->getVal('filter_status'  ,'0,1');    // 0: open, 1: in review, 2: closed, 3: declined
            $filter_type        = $request->getVal('filter_type'    ,'0,1');        // 0: Questionnaire, 1: Screenshot
            $filter_importance  = $request->getVal('filter_importance','0,1,2,3,4,5');    // 0: no, 1: -2, 2: -1, 3: 0, 4: 1, 5: 2

            $order = ' created DESC';
            $only_one_item = false;
            if($id >= 0){
                $only_one_item = true;
                $order = 'created ASC';
            }


            /* connect to the DB*/
            $dbr = wfGetDB( DB_SLAVE );
            /* get the rows from uifeedback-table */
            if($id !== -1){
                $conditions = 'id ='.$id;
            }else{
                $conditions =   'status in ('.(($filter_status !== '')?$filter_status:'-1').') and '. /* if no checkbox is selected filter for -1 (which will not be found -> empty result) */
                                'type in ('.(($filter_type !== '')?$filter_type:'-1').') and '.
                                'importance in ('.(($filter_importance !== '')?$filter_importance:'-1').')';
            }
            $res = $dbr->select(
                array('uifeedback'),
                array(
                    'id',
                    'url',
                    'type',
                    'created',
                    'task',
                    'done',         // Have you been able to carry out your intended task successfully?
                    'text1',        // some more details
                    'importance',   //
                    'happened',
                    'username',
                    'useragent',
                    'notify',
                    'CONCAT(uifeedback.id,CONCAT(\'#\',uifeedback.image_size)) as image_id',
                    'status',
                    'comment'
                ),
                $conditions,
                __METHOD__,
                array(  'ORDER BY' => $order)
            );
            /* number of rows selected */
            $count = $res->numRows();

            /* add table with filters */
            if(!$only_one_item){
                /*_____.__.__   __
                _/ ____\__|  |_/  |_  ___________
                \   __\|  |  |\   __\/ __ \_  __ \
                 |  |  |  |  |_|  | \  ___/|  | \/
                 |__|  |__|____/__|  \___  >__|
                                         \/       */
                $output_text .= '<div class="filters">';
                $output_text .= '<h2>Filter</h2>';
                $output_text .= '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
                $output_text .= '<table style="border-collapse: separate;border-spacing: 10px 5px;">';
                $output_text .= '<tr>';
                $output_text .=     '<th>Status</th>';
                $output_text .=     '<th>Importance</th>';
                $output_text .=     '<th>Type</th>';
                $output_text .= '</tr>';
                $output_text .= '<tr>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_status" value="0" '.((strpos('#'.$filter_status,'0'))?'checked':'').'>open</label></td>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_importance" value="0" '.((strpos('#'.$filter_importance,'0'))?'checked':'').'>undefined</label></td>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_type" value="1" '.((strpos('#'.$filter_type,'1'))?'checked':'').'>Screenshot</label></td>';
                $output_text .= '</tr>';
                $output_text .= '<tr>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_status" value="1" '.((strpos('#'.$filter_status,'1'))?'checked':'').'>in review</label></td>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_importance" value="1" '.((strpos('#'.$filter_importance,'1'))?'checked':'').'>--</label></td>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_type" value="0" '.((strpos('#'.$filter_type,'0'))?'checked':'').'>Questionnaire</label></td>';
                $output_text .= '</tr>';
                $output_text .= '<tr>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_status" value="3" '.((strpos('#'.$filter_status,'3'))?'checked':'').'>declined</label></td>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_importance" value="2" '.((strpos('#'.$filter_importance,'2'))?'checked':'').'>-</label></td>';
                $output_text .=     '<td>&nbsp;</td>';
                $output_text .= '</tr>';
                $output_text .= '<tr>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_status" value="2" '.((strpos('#'.$filter_status,'2'))?'checked':'').'>closed</label></td>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_importance" value="3" '.((strpos('#'.$filter_importance,'3'))?'checked':'').'>0</label></td>';
                $output_text .=     '<td>&nbsp;</td>';
                $output_text .= '</tr>';
                $output_text .= '<tr>';
                $output_text .=     '<td>&nbsp;</td>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_importance" value="4" '.((strpos('#'.$filter_importance,'4'))?'checked':'').'>+</label></td>';
                $output_text .=     '<td>&nbsp;</td>';
                $output_text .= '</tr>';
                $output_text .= '<tr>';
                $output_text .=     '<td>&nbsp;</td>';
                $output_text .=     '<td><label><input type="checkbox" name="filter_importance" value="5" '.((strpos('#'.$filter_importance,'5'))?'checked':'').'>++</label></td>';
                $output_text .=     '<td style="text-align:right;"><input type="button" value="filter" onClick="set_filter();"></td>';
                $output_text .= '</tr>';
                $output_text .= '</table>';
                $output_text .= '</form>';
                $output_text .= '</div>'; // end filters

                /*  ___________                      .________
                    \__    ___/___ ______            |   ____/
                      |    | /  _ \\____ \   ______  |____  \
                      |    |(  <_> )  |_> > /_____/  /       \
                      |____| \____/|   __/          /______  /
                                   |__|                    \/ */
                $output_text .= '<div class="stats">';
                    $output_text .= '<h2>Stats</h2>';
                    $output_text .= '<div style="float:left;">';
                        /* get name and number of closed feedback-posts */
                        $res_stats = $dbr->select(
                            array('uifeedback'),
                            array('username','count(id) as count'),
                            'username != \'\' and status = \'2\'', /* no anon users */
                            __METHOD__,
                            array('GROUP BY' => 'username','ORDER BY' => 'count DESC','LIMIT' => '5')
                        );
                        $output_text .= 'Users with most submissions (by closed feedback):';
                        $output_text .= '<table style="text-align:right;border-collapse: separate;border-spacing: 10px 5px;">';
                        /* add rows to table */
                        foreach( $res_stats as $row ){
                            $output_text .= '<tr><td>'.htmlentities($row->username).'</td><td style="text-align:right;">'.$row->count.'</td></tr>';
                        }
                        $output_text .= '</table>';
                    $output_text .= '</div>'; /* end users*/

                $output_text .= '</div>'; /* end stats */

            }else{
                /*  __________                                          .__
                    \______   \_____     ____   ____   ____ _____ ___  _|__|
                     |     ___/\__  \   / ___\_/ __ \ /    \\__  \\  \/ /  |
                     |    |     / __ \_/ /_/  >  ___/|   |  \/ __ \\   /|  |
                     |____|    (____  /\___  / \___  >___|  (____  /\_/ |__|
                                    \//_____/      \/     \/     \/       */
                /* add page-navigation */
                $output_text .='<div class="page_navi">';
                if($id>1)        $output_text .= ' <a href="'.SpecialPage::getTitleFor( 'UiFeedback' )->getFullURL().'?id='.($id-1).'" >previous</a>&nbsp;|&nbsp;';
                $output_text .= ' <a href="'.SpecialPage::getTitleFor( 'UiFeedback' )->getFullURL().'?id='.($id+1).'" >next</a>&nbsp;|&nbsp;';
                $output_text .= ' <a href="'.SpecialPage::getTitleFor( 'UiFeedback' )->getFullURL().'?id=-1" >all</a>&nbsp;';
                $output_text .= '</div>';
                /* end page-navi */
            }

            /* create the table */
            if($count>0){
                /*  ___________     ___.   .__
                    \__    ___/____ \_ |__ |  |   ____
                      |    |  \__  \ | __ \|  | _/ __ \
                      |    |   / __ \| \_\ \  |_\  ___/
                      |____|  (____  /___  /____/\___  >
                                   \/    \/          \/ */
                /* Browser and Operating-System-Icons */
                $ie = '<div class="icon ie">1</div>';
                $ff = '<div class="icon ff">2</div>';
                $ch = '<div class="icon ch">3</div>';
                $sf = '<div class="icon sf">4</div>';
                $op = '<div class="icon op">5</div>';
                $win = '<div class="icon win">1</div>';
                $mac = '<div class="icon mac">2</div>';
                $lin = '<div class="icon lin">3</div>';

                $output_text .= '<h2 style="clear:both;">Feedback</h2>';
                if(!$only_one_item)
                    $output_text .='found '.$count.' items:';

                /* Result-Table */
                $output_text .='<table class="wikitable sortable jquery-tablesorter">';
                $output_text .='<tr>';
                /*  .__                       .___.__  .__
                    |  |__   ____ _____     __| _/|  | |__| ____   ____   ______
                    |  |  \_/ __ \\__  \   / __ | |  | |  |/    \_/ __ \ /  ___/
                    |   Y  \  ___/ / __ \_/ /_/ | |  |_|  |   |  \  ___/ \___ \
                    |___|  /\___  >____  /\____ | |____/__|___|  /\___  >____  >
                         \/     \/     \/      \/              \/     \/     \/ */
                /*id*/
                $output_text .='<th scope="col" class="headerSort">ID</th>';
                /*username*/
                $output_text .='<th scope="col" class="headerSort">Username</th>';
                /*browser*/
                $output_text .= '<th scope="col" class="headerSort"></th>';
                /*OS*/
                if($can_write) $output_text .= '<th scope="col" class="headerSort"></th>';
                /*time*/
                if($only_one_item)
                    $output_text .='<th scope="col" class="headerSort headerSortUp">Timestamp</th>';
                /*type*/
                $output_text .='<th scope="col" class="headerSort">Type</th>';
                /*importance*/
                $output_text .='<th scope="col" class="headerSort">Importance</th>';
                /*happened*/
                $output_text .='<th scope="col" class="headerSort">What happened</th>';
                /*task*/
                $output_text .='<th scope="col" class="headerSort">Task</th>';
                /*done*/
                $output_text .='<th scope="col" class="headerSort">Done</th>';
                if(!$only_one_item){ // Dont display the freetext-lines in one-entry-view
                    /*text1*/
                    $output_text .='<th scope="col" class="headerSort">Details</th>';
                }
                /*status*/
                $output_text .='<th scope="col" class="headerSort headerSortUp">Status</th>';
                /*comment*/
                $output_text .='<th scope="col" class="headerSort">Notes</th>';
                /*Notify*/
                if($can_write)
                    $output_text .='<th scope="col" class="headerSort" title=" This user wants to be notified about status changes"></th>';
                /* end Row*/
                $output_text .='</tr>';

                /* Rows */
                foreach( $res as $row ) {
                    /*  __________
                        \______   \ ______  _  ________
                         |       _//  _ \ \/ \/ /  ___/
                         |    |   (  <_> )     /\___ \
                         |____|_  /\____/ \/\_//____  >
                                \/                  \/ */
                    $output_text .= '<tr>';
                    /*id*/
                    $output_text .= '<td><a href ="'.SpecialPage::getTitleFor( 'UiFeedback' )->getFullURL().'?id='.($row->id).'">'.$row->id.'</a></td>';
                    /*username*/
                    if($row->username === '') $output_text .= '<td>anonymous</td>';
                    else                      $output_text .= '<td><a href="'.Title::makeTitleSafe( NS_USER_TALK, $row->username )->getFullURL().'">'.$row->username.'</a></td>';
                    /*browser*/
                    if($can_write) $output_text .= '<td title="'.$row->useragent.'">';
                    else           $output_text .= '<td>';
                    if(strpos('#'.$row->useragent,'Chrome'))
                        $output_text .= $ch;
                    else if(strpos('#'.$row->useragent,'Safari'))
                        $output_text .= $sf;
                    else if(strpos('#'.$row->useragent,'Firefox'))
                        $output_text .= $ff;
                    else if(strpos('#'.$row->useragent,'MSIE'))
                        $output_text .= $ie;
                    else if(strpos('#'.$row->useragent,'Opera'))
                        $output_text .= $op;
                    else
                        $output_text .= '?';
                    $output_text .= '</td>';
                    /* OS - only visible to admins */
                    if($can_write){
                        $output_text .= '<td title="'.$row->useragent.'">';
                        if(strpos('#'.$row->useragent,'Windows'))
                            $output_text .= $win;
                        else if(strpos('#'.$row->useragent,'Mac OS'))
                            $output_text .= $mac;
                        else if(strpos('#'.$row->useragent,'Linux'))
                            $output_text .= $lin;
                        else
                            $output_text .= '<div>?</div>';
                        $output_text .= '</td>';
                    }
                    /*time*/
                    if($only_one_item)
                        $output_text .= '<td>'.htmlspecialchars($row->created).'</td>';
                    /*type*/
                    if($row->type === '1')  $output_text .= '<td><a href ="'.SpecialPage::getTitleFor( 'UiFeedback' )->getFullURL().'?id='.($row->id).'"><div class="icon screenshot-icon" title="Screenshot">1</div></a></td>';
                    else                    $output_text .= '<td><a href ="'.SpecialPage::getTitleFor( 'UiFeedback' )->getFullURL().'?id='.($row->id).'"><div class="icon questionnaire-icon" title="Questionnaire">2</div></a></td>';
                    /*importance*/
                    if($row->importance == 0) $output_text .= '<td>&nbsp;</td>';
                    else                    $output_text .= '<td>'.$importance_array[$row->importance].'</td>';
                    /*happened*/
                    $output_text .= '<td>'.$happened_array[$row->happened].'</td>';
                    /*task*/
                    $output_text .= '<td>'.htmlspecialchars($row->task).'</td>';
                    /*done*/
                    if(is_null($row->done))
                        $output_text .= '<td></td>';
                    else
                        $output_text .= '<td>'.$bool_array[$row->done].'</td>';
                    if(!$only_one_item){ // dont display the freetext-fields in the one-entry-only-view
                        /*text1*/
                        if(strlen($row->text1)>50) $output_text .= '<td>'.htmlspecialchars(substr($row->text1,0,50)).'...</td>';
                        else                       $output_text .= '<td>'.htmlspecialchars($row->text1).'</td>';
                    }
                    /*status*/
                    $output_text .= '<td>';
                    if($row->status == 0){
                        $output_text .= '<b>open</b><br/>';
                        if($can_write) /* only admins can change the status*/
                            $output_text .= '<a href="'.SpecialPage::getTitleFor( 'UiFeedback' )->getFullURL().'?id='.($row->id).'">review</a>';
                    }else if($row->status == 1){
                        if($can_write) /* only admins can change the status*/
                            $output_text .= '<a href="'.SpecialPage::getTitleFor( 'UiFeedback' )->getFullURL().'?id='.($row->id).'"><b>in review</b></a>';
                        else
                            $output_text .= '<b>in review</b>';
                    }else if($row->status == 2){
                        $output_text .= 'closed<br/>';
                    }else if($row->status == 3){
                        $output_text .= 'declined<br/>';
                    }
                    $output_text .= '</td>';
                    /* comment */
                    $output_text .= '<td>'.htmlspecialchars($row->comment).'</td>';
                    /* notify - only admins see this */
                    if($can_write){
                        if($row->notify) $output_text .= '<td title="This user wants to be notified about status changes"><a href="'.Title::makeTitleSafe( NS_USER_TALK, $row->username )->getFullURL().'"><div class="icon notify">1</div></a></td>';
                        else             $output_text .= '<td></td>';
                    }
                    /* end row */
                    $output_text .= '</tr>';
                }
                $output_text .= '</table>';
                /* end create table */

                /*    _________ __          __  .__          __  .__
                     /   _____//  |______ _/  |_|__| _______/  |_|__| ____   ______
                     \_____  \\   __\__  \\   __\  |/  ___/\   __\  |/ ___\ /  ___/
                     /        \|  |  / __ \|  | |  |\___ \  |  | |  \  \___ \___ \
                    /_______  /|__| (____  /__| |__/____  > |__| |__|\___  >____  >
                            \/           \/             \/               \/     \/ */
                /* statistics about presenting the different request-methods and how often they have been clicked */
                /* this information is not usefull for 'normal' users, so only admins will see it */
                if(!$only_one_item && $can_write){
                    $output_text .= '<div style="border:1px solid black;background:#FCFCFC;padding:5px;width:325px">';
                    $type_array = array('pop-up after edit','questionnaire button','screenshot button');
                    /*get rows from database*/
                    $res_stats = $dbr->select(array('uifeedback_stats'),array('type','shown', 'clicked', 'sent'),'',__METHOD__,array( 'ORDER BY' => 'type DESC' ));
                    $output_text .= 'Number of shown and clicked requests for feedback:';
                    $output_text .= '<table style="text-align:right;border-collapse: separate;border-spacing: 10px 5px;">';
                    $output_text .= '<tr><th>type</th><th>shown</th><th>clicked</th><th>sent</th></tr>';
                    /* add rows to the table */
                    foreach( $res_stats as $row_stats ){
                        $output_text .= '<tr>'.
                            '<td style="text-align:right;">'.$type_array[$row_stats->type].'</td>'.
                            '<td style="text-align:right;">'.$row_stats->shown.'</td>'.
                            '<td style="text-align:right;">'.$row_stats->clicked.'</td>'.
                            '<td style="text-align:right;">'.$row_stats->sent.'</td>'.
                            '</tr>';
                    }
                    $output_text .= '</table>';
                    $output_text .= '</div>';
                }/* end show and click stats */

            }else{
                $output_text .= '<h2 style="clear:both;">Feedback</h2>nothing found for your filters';
            }

            /*         .__               .__           .___  __
                  _____|__| ____    ____ |  |   ____   |   |/  |_  ____   _____
                 /  ___/  |/    \  / ___\|  | _/ __ \  |   \   __\/ __ \ /     \
                 \___ \|  |   |  \/ /_/  >  |_\  ___/  |   ||  | \  ___/|  Y Y  \
                /____  >__|___|  /\___  /|____/\___  > |___||__|  \___  >__|_|  /
                     \/        \//_____/           \/                 \/      \/ */
            /* One-Feedback-Item-View */
            if($only_one_item){
                $output_text .= '<h2>URL</h2>';
                $output_text .= '<a href="'.htmlspecialchars($row->url).'">'.$row->url.'</a>';

                if($row->type === '1' ){ /* screenshot Feedback */
                    $output_text .= '<h2>Comment</h2>';
                    $output_text .= htmlspecialchars($row->text1);
                    if(strlen($row->text1) == 0){
                        $output_text .= '<i>none</i>';
                    }
                }else{ /* Questionnaire Feedback */
                    $output_text .= '<h2>What happened</h2>';
                    $output_text .= htmlspecialchars($row->text1);
                    if(strlen($row->text1) == 0) $output_text .= '<i>none</i>';
                }
                $output_text .= '<div>';

                /*  __________            .__
                    \______   \ _______  _|__| ______  _  __
                     |       _// __ \  \/ /  |/ __ \ \/ \/ /
                     |    |   \  ___/\   /|  \  ___/\     /
                     |____|_  /\___  >\_/ |__|\___  >\/\_/
                            \/     \/             \/        */
                /* Review Form - only for admins */
                if($can_write){
                        $output_text .= '<div style = "float:left;">';
                            $output_text .= '<h1>Review</h1>';
                            if($row->notify)
                                $output_text .= 'Info: <i>This user wants to be notified about status changes</i><br/>';
                            $output_text .= '<form name="review" method="post" id="ui-review-form" action=""">';
                            $output_text .= '<div style="float:left;">';
                                $output_text .= 'Status:<br/>';
                                $output_text .= '<label><input type="radio" name="status" value="1" '.(($row->status==1)?'checked':'').'>in review</label><br/>';
                                $output_text .= '<label><input type="radio" name="status" value="2" '.(($row->status==2)?'checked':'').'>closed</label><br/>';
                                $output_text .= '<label><input type="radio" name="status" value="3" '.(($row->status==3)?'checked':'').'>declined</label><br/>';
                                $output_text .= '</div>';
                                $output_text .= '<div style="float:left;margin-left:20px">';
                                $output_text .= '<label>Notes:<br/><textarea name="comment" rows="5" style="width:300px">'/*.$row->comment*/.'</textarea></label>';
                                $output_text .= '<input type="hidden" name="id" value="'.$id.'">';
                                $output_text .= '<input type="hidden" name="method" value="review">';
                                $output_text .= '<br/><input type="button" value="send" onClick="send_review();">';
                            $output_text .= '</div></form>';
                        $output_text .= '</div>';
                }
                /* previous Comments/Reviews */
                $res = $dbr->select(
                    array('uifeedback_reviews'),
                    array('created','reviewer','status','comment'),
                    array('feedback_id' => $id),
                    __METHOD__,
                    array('ORDER BY' => 'created DESC')
                );
                if($res->numRows()>0){
                    $output_text .= '<div style = "clear:both;">';
                    $output_text .= '<h1>Previous Notes</h1>';
                    $output_text .= '<ul>';
                    foreach( $res as $review_row ) {
                        $output_text .= '<li>'.$review_row->created.' - '.$review_row->reviewer.' - <b>'.$status_array[$review_row->status].'</b>:<br/>'.$review_row->comment.'</li>';
                    }
                    $output_text .= '</ul>';
                    $output_text .= '</div>';

                    $output_text .= '</div>';
                }
                /* end previous comments */

                /*    _________                                         .__            __
                     /   _____/ ___________   ____   ____   ____   _____|  |__   _____/  |_
                     \_____  \_/ ___\_  __ \_/ __ \_/ __ \ /    \ /  ___/  |  \ /  _ \   __\
                     /        \  \___|  | \/\  ___/\  ___/|   |  \\___ \|   Y  (  <_> )  |
                    /_______  /\___  >__|    \___  >\___  >___|  /____  >___|  /\____/|__|
                            \/     \/            \/     \/     \/     \/     \/             */
                /* Screenshot */
                if($row->type == '1'){
                    $output_text .= '<div style="clear: both;">';
                    $output_text .= '<h2>Screenshot:</h2>';
                    $output_text .= '<img style="max-width:800px;cursor:pointer;" src="'.SpecialPage::getTitleFor( 'UiFeedback_api' )->getFullURL().'?getScreenshotByID='.$row->id.'" alt="screenshot" onclick="$(this).css(\'max-width\',\'\').css(\'cursor\',\'auto\');">';
                    $output_text .= '</div>';
                }
            }

            /*       ____.                     _________            .__        __
                    |    |____ ___  _______   /   _____/ ___________|__|______/  |_
                    |    \__  \\  \/ /\__  \  \_____  \_/ ___\_  __ \  \____ \   __\
                /\__|    |/ __ \\   /  / __ \_/        \  \___|  | \/  |  |_> >  |
                \________(____  /\_/  (____  /_______  /\___  >__|  |__|   __/|__|
                              \/           \/        \/     \/         |__|         */
            /* filter funciton and review_send*/
            $output_text .= '<script>';
            $output_text .= "
            function set_filter(e){
                 var filter_status = '';
                 var filter_importance = '';
                 var filter_type = '';
                 $('input[name=filter_status]:checked').each(function(){
                    filter_status += $(this).val()+',';
                 });
                 $('input[name=filter_importance]:checked').each(function(){
                    filter_importance += $(this).val()+',';
                 });
                 $('input[name=filter_type]:checked').each(function(){
                    filter_type += $(this).val()+',';
                 });
                 console.log('filter_status: '+filter_status);
                 console.log('filter_importance: '+filter_importance);
                 console.log('filter_type: '+filter_type);
                 var new_url = mw.util.wikiGetlink('Special:UiFeedback');
                 new_url    +='?filter_status='+filter_status.slice(0, -1);
                 new_url    +='&filter_importance='+filter_importance.slice(0, -1);
                 new_url    +='&filter_type='+filter_type.slice(0, -1);
                 window.location.href = new_url;
            }
            function send_review(){
                $.post(mw.util.wikiGetlink('Special:UiFeedback_api'), $('#ui-review-form').serializeArray())
                .done(function(data) {
                  window.location=mw.util.wikiGetlink('Special:UiFeedback');
                });
            }

            ";
            $output_text .= '</script>';
        }

        /* write to output */
        $output->addHTML( $output_text );
    }


    function addStatusLinks($status){

    }

}

