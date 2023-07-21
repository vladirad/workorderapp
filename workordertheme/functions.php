<?php

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );
function enqueue_parent_styles() {
   wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

add_action('rest_api_init', function () {
  register_rest_route( 'wp/v2', '/radni_nalog/user/(?P<user_id>\d+)',array(
                'methods'  => 'GET',
                'callback' => 'get_user_posts',
                'permission_callback' => '__return_true',
                'args' => array(
                  'page' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                    }
                  ),
                  'search' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                    }
                  ),
                  'status' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                    }
                  ),
                  'model' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                    }
                  ),
                  'datefrom' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                    }
                  ),
                  'dateto' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                    }
                  ),
                )
      ));

  register_rest_route( 'wp/v2', '/radni_nalog/(?P<id>\d+)/napomena/',array(
                'methods'  => 'POST',
                'callback' => 'add_napomena',
                'permission_callback' => '__return_true',
                'args' => array(
                  'napomena' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                    }
  ))));

  register_rest_route( 'wp/v2', '/radni_nalog/(?P<id>\d+)/status/',array(
                'methods'  => 'POST',
                'callback' => 'change_status',
                'permission_callback' => '__return_true',
                'args' => array(
                  'status' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                    }
  ))));

  register_rest_route( 'wp/v2', '/radni_nalog/(?P<id>\d+)/napomena/',array(
                'methods'  => 'GET',
                'callback' => 'get_napomene',
                'permission_callback' => '__return_true'
    ));

  register_rest_route( 'wp/v2', '/radni_nalog/(?P<id>\d+)/rezervni_dio/',array(
                'methods'  => 'POST',
                'callback' => 'add_rezervni',
                'permission_callback' => '__return_true',
                'args' => array(
                  'rezervni' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                    }
  ))));

  register_rest_route( 'wp/v2', '/radni_nalog/(?P<id>\d+)/uredi',array(
                'methods'  => 'POST',
                'callback' => 'edit_nalog',
                'permission_callback' => '__return_true',
                'args' => array(
                  'model' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                  }),
                  'serijski' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                })
  )));

  register_rest_route( 'wp/v2', '/radni_nalog/(?P<id>\d+)/rezervni_dio/',array(
                'methods'  => 'GET',
                'callback' => 'get_rezervni',
                'permission_callback' => '__return_true'
  ));
  
  register_rest_route( 'wp/v2', '/notifikacija/user/(?P<user_id>\d+)',array(
                'methods'  => 'GET',
                'callback' => 'get_user_notifications',
                'permission_callback' => '__return_true'
      ));

  register_rest_route( 'wp/v2', '/notifikacija/user/(?P<user_id>\d+)',array(
                'methods'  => 'DELETE',
                'callback' => 'delete_user_notifications',
                'permission_callback' => '__return_true'
      ));
  register_rest_route( 'wp/v2', '/notifikacija/(?P<id>\d+)/status',array(
                'methods'  => 'POST',
                'callback' => 'change_notification_status',
                'permission_callback' => '__return_true',
                'args' => array(
                  'status' => array(
                    'validate_callback' => function($param, $request, $key) {
                      return $param;
                  })
      )));

});

function add_rezervni($request) {
  $rezervni = $_POST['rezervni'];
 //$timenow = new DateTime();
  $time = date('Ymd H:i:s');
  $id = $request->get_param( 'id' );
  
  $post = get_post($id);
  
  $author_email = get_the_author_meta( 'user_email', $post->post_author );
  $email_subject = 'Novi zahtjev za rezervni dio - ' . $post->post_title;
  $headers = array('Content-Type: text/html; charset=UTF-8');
  $email_text = 'Poslan je novi zahtjev za rezervni dio na  aplikaciji - možete ga pogledati na linku <a href="' . $post_edit_link . '">' . $post->post_title . '</a>';
  wp_mail( $author_email, $email_subject, $email_text, $headers);
  
  $notarr = array(
    'post_title' => $post->post_title,
    'post_status' => 'publish',
    'post_type' => 'notifikacija'
  );
     
  $notifikacija = wp_insert_post( $notarr, false, true );
  
  update_field('radni_nalog', $post->ID, $notifikacija);
  update_field('ime_naloga', $post->post_title, $notifikacija);
  update_field('tip_notifikacije', 'rezervni', $notifikacija);
  
  $getrows = get_field('field_61c2fa10e1505', $id);

  $i = 0;
  
  $row = array(
  	'field_61c2fa110acb6' => $rezervni,
   'field_61c2fa110acfc' => $time
  );
  
  if(!$getrows) {
  	$newrows = array();
  } else {
  	$newrows = $getrows; 
  }

  array_push($newrows, $row);
 
  update_field('field_61c2fa10e1505', $newrows, $id);
}

function get_rezervni($request) {
 $id = $request->get_param( 'id' );
 
 $rezervni = get_field('field_61c2fa10e1505', $id);

 return rest_ensure_response( $rezervni );
}

function add_napomena($request) {
  $napomena = $_POST['napomena'];
  //$timenow = new DateTime();
  $time = date('Ymd H:i:s');
  $id = $request->get_param( 'id' );
  
  $post = get_post($id);

  $author_email = get_the_author_meta( 'user_email', $post->post_author );
  $email_subject = 'Nova napomena - ' . $post->post_title;
  $headers = array('Content-Type: text/html; charset=UTF-8');
  $email_text = 'Poslana je nova napomena na  aplikaciji - možete ga pogledati na linku <a href="' . $post_edit_link . '">' . $post->post_title . '</a>';
  wp_mail( $author_email, $email_subject, $email_text, $headers);

  $notarr = array(
    'post_title' => $post->post_title,
    'post_status' => 'publish',
    'post_type' => 'notifikacija'
  );
     
  $notifikacija = wp_insert_post( $notarr, false, true );

  update_field('radni_nalog', $post->ID, $notifikacija);
  update_field('ime_naloga', $post->post_title, $notifikacija);
  update_field('tip_notifikacije', 'napomena', $notifikacija);
 
  $getrows = get_field('field_61c2fa10e14cc', $id);

  $row = array(
 	  'field_61c2fa1105aec' => $napomena,
    'field_61c2fa1105b31' => $time
  );

 if(!$getrows) {
 	$newrows = array();
 } else {
 	$newrows = $getrows; 
 }

 array_push($newrows, $row);
 
 update_field('field_61c2fa10e14cc', $newrows, $id);
}

function get_napomene($request) {
 $id = $request->get_param( 'id' );
 
 $napomene = get_field('field_61c2fa10e14cc', $id);

 return rest_ensure_response( $napomene );
}

function change_status($request) {
  $status = $request->get_param( 'status' );
  $id = $request->get_param( 'id' );
  
  update_field('status', $status, $id);
  
  $nalog = get_post($id);
  $nalog->meta = get_fields($nalog->ID);
  $nalog->meta['broj_telefona'] = array();
  $nalog->meta['broj_telefona']['number'] = get_post_meta( $id, 'broj_telefona', true );

  $notarr = array(
    'post_title' => $nalog->post_title,
    'post_status' => 'publish',
    'post_type' => 'notifikacija'
  );
  
  $serviser = get_field('serviser', $nalog->ID);
  
  $serviser_id = $serviser['ID'];
  $notifikacija = wp_insert_post( $notarr, false, true );
  
  update_field('radni_nalog', $nalog->ID, $notifikacija);
  update_field('ime_naloga', $nalog->post_title, $notifikacija);
  update_field('serviser', $serviser_id, $notifikacija);
  update_field('tip_notifikacije', 'promjena_statusa', $notifikacija);
  update_field('novi_status', $status, $notifikacija);

  return rest_ensure_response($nalog);
}


function get_user_posts($request) {
	$user_id = $request->get_param( 'user_id' );
	$page = $request->get_param( 'page' );
  $search = $request->get_param( 'search' );
  $model = $request->get_param( 'model' );
  $status = $request->get_param( 'status' );
  $datefrom = $request->get_param( 'datefrom' );
  $dateto = $request->get_param( 'dateto' );

  if($page) {
    $offset = 10 * ($page - 1);
    $args['offset'] = $offset;
  }

  $args = array(
      'post_type' => 'radni_nalog',
      'status' => 'publish',
      'meta_query' => array(
          array(
              'key'     => 'serviser',
              'value'   => $user_id,
              'compare' => '='
          ),
      ),
      'meta_key' => 'serviser',
      'meta_value' => $user_id,
      'posts_per_page' => 10,
      'offset' => $offset
  );



  if($datefrom && $dateto) {
    $args['date_query'] = array(
        array(
            'column' => 'post_date',
            'after'     => $datefrom,
            'before'    => $dateto,
            'inclusive' => true
        )
    );
  }

  if($datefrom && !$dateto) {
    $args['date_query'] = array(
        array(
            'column' => 'post_date',
            'after'     => $datefrom,
            'inclusive' => true
        )
    );
  }

  if(!$datefrom && $dateto) {
    $args['date_query'] = array(
        array(
            'column' => 'post_date',
            'before'    => $dateto,
            'inclusive' => true
        )
    );
  }

  if($model) {
    $args['meta_query'][] = array(
      'key'     => 'model',
      'value'   => $model,
      'compare' => 'LIKE'
    );
  }

  if($status) {
    $args['meta_query'][] = array(
      'key'     => 'status',
      'value'   => $status,
      'compare' => 'LIKE'
    );
  }

  if($search) {
    $args['s'] = $search;
  }

	$nalozi = get_posts($args);

	foreach ($nalozi as $key => $nalog) {
		$nalog->meta = get_fields($nalog->ID);
    if($nalog->meta['status'] == 'poslan') {
      update_field('status', 'otvoren', $nalog->ID);
      $nalog->meta['status'] = 'otvoren';
    }

    if($nalog->meta['status'] == 'skica') {
      unset($nalozi[$key]);
    }

    if($nalog->meta['status'] == 'zamjenako') {
      unset($nalozi[$key]);
    }


		$nalog->meta['broj_telefona'] = array();
    $nalog->meta['broj_telefona']['number'] = get_post_meta( $nalog->ID, 'broj_telefona', true );

	}

	return rest_ensure_response( $nalozi );
}

function get_user_notifications($request) {
  $user_id = $request->get_param( 'user_id' );

  $notifikacije = get_posts( 
    array(
      'post_type' => 'notifikacija',
      'status' => 'publish',
      'meta_key' => 'serviser',
      'meta_value' => $user_id,
      'posts_per_page' => -1,
    )
  );

  foreach ($notifikacije as $notifikacija) {
    $notifikacija->meta = get_fields($notifikacija->ID);
  }

  //$response = json_encode($nalozi);

  return rest_ensure_response( $notifikacije );
}

function delete_user_notifications($request) {
  $user_id = $request->get_param( 'user_id' );

  $notifikacije = get_posts( 
    array(
      'post_type' => 'notifikacija',
      'status' => 'publish',
      'meta_key' => 'serviser',
      'meta_value' => $user_id,
      'posts_per_page' => -1,
    )
  );

  foreach ($notifikacije as $notifikacija) {
    $post = array( 'ID' => $notifikacija->ID, 'post_status' => 'draft' );
    wp_update_post($post);
  }

  return rest_ensure_response( $notifikacije );
}

function change_notification_status($request) {
  $status = $request->get_param( 'status' );
  $id = $request->get_param( 'id' );

  update_field('status', $status, $id);
  $notifikacija = get_post($id);
  $notifikacija->meta = get_fields($notifikacija->ID);
  $notifikacija->meta['status'] = $status;

  return rest_ensure_response($notifikacija);
}


add_action( 'rest_api_init', 'create_api_radni_nalog_meta_field' );
 
function create_api_radni_nalog_meta_field() {

    // register_rest_field ( 'name-of-post-type', 'name-of-field-to-return', array-of-callbacks-and-schema() )
    register_rest_field( 'radni_nalog', 'meta', array(
           'get_callback'    => 'get_radni_nalog_meta_for_api',
           'schema'          => null,
        )
    );
}
 
function get_radni_nalog_meta_for_api( $object ) {
    //get the id of the post object array
    $post_id = $object['id'];

    $meta = get_fields($post_id);
    $meta['broj_telefona'] = array();
    $meta['broj_telefona']['number'] = get_post_meta( $post_id, 'broj_telefona', true );
 
    //return the post meta
    return $meta;
}

function edit_nalog($request) {
    
    $nalog_id = $request->get_param( 'id' );
    $model = $request->get_param( 'model' );
    $serijski = $request->get_param( 'serijski' );

    if($model) {
      update_field('model', $model, $nalog_id);
    }
    
    if ($serijski) {
      update_field('serijski_broj', $serijski, $nalog_id);
    }
    
    $nalog = get_post($nalog_id);
    $nalog->meta = get_fields($nalog->ID);
    $nalog->meta['broj_telefona'] = array();
    $nalog->meta['broj_telefona']['number'] = get_post_meta( $nalog_id, 'broj_telefona', true );

    return rest_ensure_response($nalog);
}

function new_radni_nalog($post_id, $post) {

      if($post->post_title) {
        $title = $post->post_title;
      } else {
        if(!$_POST['acf']['field_61dec7953c1e1'] && !$_POST['acf']['field_61dec7953c1e1']) {
          $title = ucwords($_POST['acf']['field_619f695f095a8']) . ' ' . ucwords($_POST['acf']['field_619f69e2095a9']) . ' - ' . $_POST['acf']['field_619f6a57e89ef'];
        } else {
          $title = ucwords($_POST['acf']['field_61dec7953c1e1']) . ' ' . ucwords($_POST['acf']['field_61dec7953c219']) . ' - ' . $_POST['acf']['field_61dec7953c24f'];
        }
      }

      $alreadypublished = get_post_meta( $post_id, 'alreadypublished', true );

      if(!$alreadypublished) {
        
        update_post_meta( $post_id, 'alreadypublished', true );

        $post_edit_link = get_edit_post_link( $post->ID );
        $author_email = get_the_author_meta( 'user_email', $post->post_author );
        $email_subject = 'Novi nalog - ' . $title;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $email_text = 'Objavljen je novi nalog na proservis aplikaciji.<br><br>Novi Nalog možete pregledati na linku <a href="' . $post_edit_link . '">' . $title . '</a>';
        wp_mail( $author_email, $email_subject, $email_text, $headers);

        $notarr = array(
          'post_title' => $title,
          'post_status' => 'publish',
          'post_type' => 'notifikacija'
        );
        
        $notifikacija = wp_insert_post( $notarr, false, true );
        $status_naloga = get_field( 'status', $post->ID );

        $serviser_id = get_post_meta( $post->ID, 'serviser', true );
        if(!$serviser_id){ 
          $serviser_id = $_POST['acf']['field_61c2fa10e1492']; 
        }
        $serviser = get_user_by('ID', $serviser_id);

        if($serviser) {
          $servisermail = $serviser->user_email;
          $servisermailtext = 'Dobili ste novi radni nalog na  aplikaciji.<br><br>Lijep pozdrav,<br><br>';
          update_field('serviser', $serviser_id, $notifikacija);
          $msgbody = "Dobili ste novi radni nalog - " . $title . ".";
          $send = pn_send_push_notification($serviser_id, "Proservis", $msgbody, $post->ID);
          wp_mail( $servisermail, $email_subject, $servisermailtext, $headers);
        }

        update_field('radni_nalog', $post->ID, $notifikacija);
        update_field('ime_naloga', $post->post_title, $notifikacija);
        update_field('tip_notifikacije', 'novi_nalog', $notifikacija);


        if($post->post_content) {
          $mycontent = $post->post_content;
        } else {

          $acf = $_POST['acf'];

          unset($acf['field_619f6a13095ab']);
          unset($acf['field_61c2fa10e1492']);
          unset($acf['field_61c2fa10e14cc']);
          unset($acf['field_61c2fa10e1505']);

          $mycontent = implode(" | ", $acf);

          $drzave = get_the_terms( $post, 'drzava' );

          if($drzave) {
            foreach ($drzave as $drzava) {
              $drz = $drzava->name;
            }

            $mycontent = $mycontent . $drz; 
          }
                
        }


        $my_post = array(
          'ID'           =>  $post_id,
          'post_title'   =>  $title,
          'post_content'   =>  $mycontent
        );

        wp_update_post( $my_post );
      } 
}

add_action( 'publish_radni_nalog', 'new_radni_nalog', 10, 2 );

function new_uredaj($post_id, $post) {
      $alreadypub = get_post_meta( $post_id, 'alreadypub', true );

      if(!$alreadypub) {
        update_post_meta( $post_id, 'alreadypub', true );

        $title = $_POST['acf']['field_619f6b3bd5657'] . ' - ' . $_POST['acf']['field_619f6b46d5659'];

        $my_post = array(
          'ID'           =>  $post_id,
          'post_title'   =>  $title,
          'post_content'   =>  $title
        );

        wp_update_post( $my_post );
      }
}

//add_action( 'publish_uredaj', 'new_uredaj', 10, 2 );

function new_prijava_kvara($ID, $post ) {

  $post_edit_link = get_edit_post_link( $ID );
  $author_email = get_the_author_meta( 'user_email', $post->post_author );
  $email_subject = 'Nova prijava kvara - ' . $post->post_title;
  $headers = array('Content-Type: text/html; charset=UTF-8');
  $email_text = 'Dodan je novi nalog putem prijave kvara na proservis aplikaciji.<br><br> Novi Nalog možete pregledati na linku <a href="' . $post_edit_link . '">' . $post->post_title . '</a>';
  wp_mail( $author_email, $email_subject, $email_text, $headers);

  $user_email = $_POST['acf']['field_6229bb48c2ca0'];
  $user_email_subject = 'Prijava Kvara uspješna - ';
  $user_email_text = 'Poštovani,<br><br>Uspješno ste prijavili kvar. Serviser će Vas kontaktirati u najkraćem roku. Hvala Vam na strpljenju. <br><br>Lijep pozdrav. ';
  wp_mail( $user_email, $user_email_subject, $user_email_text, $headers);

  
}

add_action( 'draft_radni_nalog', 'new_prijava_kvara', 10, 2 );

add_filter( 'bdpwr_code_email_subject' , function( $subject ) {
  return 'Reset lozinke';
}, 10 , 1 );

add_filter( 'bdpwr_code_email_text' , function( $text , $email , $code , $expiry ) {
  //$text = 'testiram tekst';
  return $text;
}, 10 , 4 );

add_filter('jwt_auth_token_before_dispatch', 'mod_jwt_auth_token_before_dispatch', 10, 2);

function mod_jwt_auth_token_before_dispatch($data, $user) {
     $data['id'] = $user->data->ID;
     
     return $data;
}

/*add_action('acf/save_post', 'my_acf_save_post', 5);
function my_acf_save_post( $post_id ) {

    $post = get_post($post_id);
    
    if(!$post->post_title || !$post->post_content ) {
            
      if($post->post_title) {
        $naslov = $post->post_title;
      } else {
        $naslov = $_POST['acf']['field_619f695f095a8'] . ' ' . $_POST['acf']['field_619f69e2095a9'] . ' - ' . $_POST['acf']['field_619f6a57e89ef'];
      }

      if($post->post_content) {
        $mycontent = $post->post_content;
      } else {

        $acf = $_POST['acf'];

        unset($acf['field_619f6a13095ab']);
        unset($acf['field_61c2fa10e1492']);
        unset($acf['field_61c2fa10e14cc']);
        unset($acf['field_61c2fa10e1505']);

        $mycontent = implode(" | ", $acf);

        $drzave = get_the_terms( $post, 'drzava' );

        if($drzave) {
          foreach ($drzave as $drzava) {
            $drz = $drzava->name;
          }

          $mycontent = $mycontent . $drz; 
        }
              
      }

      $my_post = array(
          'ID'           =>  $post_id,
          'post_title'   =>  $naslov,
          'post_content'   =>  $mycontent
      );

      wp_update_post( $my_post );
    }

}*/

function pn_send_push_notification($user_id, $msgtitle, $msgbody, $nalog_id)
{
    $server_key = 'AAAARLsTOg8:APA91bFr26T-6glOBypapTzGELDUm1ztEbssB9x9-lo8ykxfznz39JiElGtnTK7RBthwP3wYi75qvnP88wD8_G7UquoKubiqRBFSpNAjy8KVo-L4D0p8Fk5JGTZrIp7SN6amlcLbuHVP';
    $url = 'https://fcm.googleapis.com/fcm/send';
    $fields['to'] = '/topics/' . $user_id;
    $fields['notification'] = array(
        'body' => $msgbody,
        'title' => $msgtitle
    );
    $fields['data'] = array(
        'nalog_id' => $nalog_id
    );

    $headers = array(
        'Content-Type:application/json',
        'Authorization:key=' . $server_key
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    curl_exec($ch);
    curl_close($ch);

}

function add_rn_acf_columns ( $columns ) {
   return array_merge ( $columns, array ( 
     'redni_broj' => __ ( 'Redni Broj' ),
     'grad' => __ ( 'Grad' ),
     'serviser'   => __ ( 'Serviser' ),
     'status'   => __ ( 'Status' )
   ) );
 }
 add_filter ( 'manage_radni_nalog_posts_columns', 'add_rn_acf_columns' );

function add_not_acf_columns ( $columns ) {
   return array_merge ( $columns, array ( 
     'tip' => __ ( 'Tip' ),
     'nalog' => __ ( 'Nalog' )
   ) );
 }
 add_filter ( 'manage_notifikacija_posts_columns', 'add_not_acf_columns' );

 function radni_nalog_custom_column ( $column, $post_id ) {
  switch ( $column ) {
    case 'redni_broj':
      echo get_post_meta ( $post_id, 'redni_broj', true );
      break;
    case 'serviser':
      $serviser_id = get_post_meta ( $post_id, 'serviser', true );
      if($serviser_id) {
        $userdata = get_userdata($serviser_id);
        echo $userdata->first_name . ' ' . $userdata->last_name;
      }
      
      break;
    case 'grad':
      echo ucwords(get_post_meta ( $post_id, 'grad', true ));
      break;
    case 'status':
      $status = get_post_meta ( $post_id, 'status', true );
      if($status == 'zamjena') {
        $status = 'Odobrena zamjena';
      }
      if($status == 'zamjenako') {
        $status = 'Odobrena zamjena K.O.';
      }
        
      echo ucwords($status);
      break;
  }
 }
 add_action ( 'manage_radni_nalog_posts_custom_column', 'radni_nalog_custom_column', 10, 2 );

 function notifikacija_custom_column ( $column, $post_id ) {
  switch ( $column ) {
    case 'tip':
      echo str_replace('_', ' ', ucwords(get_post_meta ( $post_id, 'tip_notifikacije', true )));
      break;
    case 'nalog':
      $nalogid = get_post_meta ( $post_id, 'radni_nalog', true );
      $nalog = get_post($nalogid);
      echo '<a href="'.get_edit_post_link($nalogid).'">Radni Nalog '. $nalog->post_title . '</a>';
      break;

  }
 }
 add_action ( 'manage_notifikacija_posts_custom_column', 'notifikacija_custom_column', 10, 2 );


add_filter('post_class', 'custom_radni_nalog_classes');

function custom_radni_nalog_classes($classes)
{
    global $post; 
    $post_type = get_post_type($post);  
    if($post_type == 'radni_nalog') {
      $nalogstatus = get_post_meta ( $post->ID, 'status', true );

      if($nalogstatus == 'poslan') {
        $classes[] = 'nalog-poslan';
      } 

      if($nalogstatus == 'skica') {
        $classes[] = 'nalog-skica';
      } 

      if($nalogstatus == 'otvoren') {
        $classes[] = 'nalog-otvoren';
      } 

      if($nalogstatus == 'priprema') {
        $classes[] = 'nalog-priprema';
      } 

      if($nalogstatus == 'cekanje') {
        $classes[] = 'nalog-cekanje';
      } 

      if($nalogstatus == 'zatvoren') {
        $classes[] = 'nalog-zatvoren';
      }

      if($nalogstatus == 'zamjena') {
        $classes[] = 'nalog-zatvoren';
      }

      if($nalogstatus == 'zamjenako') {
        $classes[] = 'nalog-zatvoren';
      }
    
    }

    if($post_type == 'notifikacija') {
      $tip = get_post_meta ( $post->ID, 'tip_notifikacije', true );

      if($tip == 'novi_nalog') {
        $classes[] = 'not-novi';
      } 

      if($tip == 'promjena_statusa') {
        $classes[] = 'not-promjena';
      } 

      if($tip == 'napomena') {
        $classes[] = 'not-napomena';
      } 

      if($tip == 'rezervni') {
        $classes[] = 'not-rezervni';
      } 

      if($tip == 'azuriran_nalog') {
        $classes[] = 'not-azuriran';
      } 
    
    }

    return $classes;
}



add_action( 'admin_head-edit.php', function(){
    
    ?>
    <style>

    tr.type-radni_nalog {
      font-size: 20px!important;
    }

    tr.nalog-poslan {
      background-color: #ADD8E6 !important;
    }

    tr.nalog-otvoren {
      background-color: #90EE90 !important;
    }

    tr.nalog-skica {
      background-color: #C5B4E3 !important;
    }

    tr.nalog-priprema {
      background-color: #FFFFA7 !important;
    }

    tr.nalog-cekanje {
      background-color: #CCCCCC !important;
    }

    tr.nalog-zatvoren {
      background-color: #FFCCCB !important;
    }

    tr.not-novi {
      background-color: #ADD8E6 !important;
    }

    tr.not-promjena {
      background-color: #90EE90 !important;
    }

    tr.not-napomena {
      background-color: #FFFFA7 !important;
    }

    tr.not-rezervni {
      background-color: #FFCCCB !important;
    }

    tr.not-azuriran {
      background-color: #ADD8E6 !important;
    }

    td.column-status {
      font-weight: bold;
    }
    
    </style>
    <?php
});



add_filter( 'gettext', 'change_publish_button', 10, 2 );

function change_publish_button( $translation, $text ) {

if ( $text == 'Publish' )
    return 'Proslijedi';

return $translation;
}

/** Create the filter dropdown */
add_action( 'restrict_manage_posts', 'wpse45436_admin_posts_filter_restrict_manage_posts' );
 
function wpse45436_admin_posts_filter_restrict_manage_posts(){
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
 
    //add filter to the post type you want
    if ('radni_nalog' == $type){ //Replace NAME_OF_YOUR_POST with the name of custom post
        $values = array(
            'Poslan' => 'poslan', //Replace label1 with name and value1 with the value of custom field
            'Otvoren' => 'otvoren', //Replace label1 with name and value1 with the value of custom field
            'U Pripremi' => 'priprema', //Replace label1 with name and value1 with the value of custom field
            'Na Čekanju' => 'cekanje', //Replace label1 with name and value1 with the value of custom field
            'Zatvoren' => 'zatvoren', //Replace label1 with name and value1 with the value of custom field
            'Odobrena zamjena' => 'zamjena', //Replace label1 with name and value1 with the value of custom field
            'Odobrena zamjena K.O.' => 'zamjenako', //Replace label1 with name and value1 with the value of custom field
            
        );
        ?>
        <select name="status">
<option value=""><?php _e('Filtriraj po Statusu', 'wose45436'); ?></option>
        <?php $current_v = isset($_GET['status'])? $_GET['status']:''; foreach ($values as $label => $value) {
                printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $value,
                        $value == $current_v? ' selected="selected"':'',
                        $label
                    );
                }
        ?>
        </select>

        <?php  

      $args = array(
        'role'    => 'serviser',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );
    $serviseri = get_users( $args );

    $servarr = array();

    foreach ($serviseri as $serviser) {
      $servarr[$serviser->first_name . ' ' . $serviser->last_name] = $serviser->ID;
    }
 
    //add filter to the post type you want
    if ('radni_nalog' == $type){ //Replace NAME_OF_YOUR_POST with the name of custom post
        $values2 = $servarr;
        ?>
        <select name="serviser">
<option value=""><?php _e('Filtriraj po Serviseru', 'wose45436'); ?></option>
        <?php $current_v = isset($_GET['serviser'])? $_GET['serviser']:''; foreach ($values2 as $label => $value) {
                printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $value,
                        $value == $current_v? ' selected="selected"':'',
                        $label
                    );
                }
        ?>
        </select>
        <?php }
      } 
}

add_filter( 'parse_query', 'wpr_manager_filter' );
function  wpr_manager_filter($query) {
  
  global $pagenow;
  global $typenow;

  $current_page = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

  if ( is_admin()
    && 'radni_nalog' == $typenow
    && 'edit.php' == $pagenow
  )
  {

    $meta_query = array();

    

    if ( isset($_GET['status']) && $_GET['status'] != '') {
      $queryParamsCounter++;
      $meta_query[] = array(
        'key'     => 'status',
        'value'   => $_GET['status'],
      );
    }
      
    if ( isset($_GET['serviser']) && $_GET['serviser'] != '') {
      $queryParamsCounter++;
      $meta_query[] = array(
        'key'     => 'serviser',
        'value'   => $_GET['serviser'],
      );
    }

    if ($queryParamsCounter > 1) {
      $meta_query['relation'] = 'AND';
    }

    $query->set( 'meta_query', $meta_query);
  }
}



global $wp_roles; 
$wp_roles->add_cap( 'administrator', 'view_rezervni' ); 
$wp_roles->add_cap( 'editor', 'view_rezervni' ); 
$wp_roles->add_cap( 'skladistar', 'view_rezervni' ); 
/**
 * Create admin Page to list unsubscribed emails.
 */
 // Hook for adding admin menus
 add_action('admin_menu', 'add_rezervni_dijelovi_page');
 
 // action function for above hook
 
/**
 * Adds a new top-level page to the administration menu.
 */
function add_rezervni_dijelovi_page() {
     add_menu_page(
        __( 'Rezervni Dijelovi', 'textdomain' ),
        __( 'Rezervni Dijelovi', 'textdomain' ),
        'view_rezervni',
        'rezervni_dijelovi',
        'rezervni_dijelovi_page_callback',
        ''
    );
}


 add_action('admin_menu', 'add_napomene_page');
/**
 * Adds a new top-level page to the administration menu.
 */
function add_napomene_page() {
     add_menu_page(
        __( 'Napomene', 'textdomain' ),
        __( 'Napomene', 'textdomain' ),
        'view_rezervni',
        'napomene',
        'napomene_page_callback',
        ''
    );
}

add_action( 'wp_ajax_nopriv_change_rezervni', 'change_rezervni' );
add_action( 'wp_ajax_change_rezervni', 'change_rezervni' );

function change_rezervni() {
    $nalog_id = $_POST['nalog'];
    $index = $_POST['index'];
    $sifra = $_POST['sifra'];
    $status = $_POST['status'];
    $komada = $_POST['komada'];

    $dijelovi = get_field('dijelovi', $nalog_id);

    $dijelovinew = $dijelovi;

    if($sifra) {
      $dijelovinew[$index]['sifra'] = $sifra;
      echo $sifra;
    }

    if($status) {
      $dijelovinew[$index]['status'] = $status;
      echo $status;
      $notarr = array(
        'post_title' => get_the_title($nalog_id),
        'post_status' => 'publish',
        'post_type' => 'notifikacija'
      );
      
      $notifikacija = wp_insert_post( $notarr, false, true );
      update_field('radni_nalog', $nalog_id, $notifikacija);
      update_field('ime_naloga', get_the_title($nalog_id), $notifikacija);
      update_field('tip_notifikacije', 'rezervni_status', $notifikacija);
      update_field('novi_status_rez', $status, $notifikacija);

    }

    if($komada) {
      $dijelovinew[$index]['komada'] = $komada;
      echo $komada;
    }

    update_field('dijelovi', $dijelovinew, $nalog_id);
    
    die;
}

add_action( 'wp_ajax_nopriv_filter_rezervni', 'filter_rezervni' );
add_action( 'wp_ajax_filter_rezervni', 'filter_rezervni' );

function filter_rezervni() {
    $status = $_POST['statusrez'];
    $serviser = $_POST['serviserrez'];

    $args = array(
      'post_type' => 'radni_nalog',
      'posts_per_page' => 1000,
      'orderby' => 'date',
      'order' => 'desc'
    );

    $nalozi = get_posts( $args );

    $html = '<table id="tabeladijelova" cellpadding="5px" cellspacing="0" border="1px">
        <tr>
          <th>Broj Naloga</th>
          <th>Model</th>
          <th>Kataloški broj</th>
          <th>Ime stranke</th>
          <th>Grad</th>
          <th>Serviser</th>
          <th>Opis dijela</th>
          <th>Vrijeme</th>
          <th>Šifra dijela</th>
          <th>Komada</th>
          <th>Status</th>
        </tr>';

    foreach ($nalozi as $nalog) {

      $dijelovi = get_field('dijelovi', $nalog->ID);
      $servisernal = get_field('serviser', $nalog->ID);

      $servisertab = $servisernal['user_firstname'] . ' ' . $servisernal['user_lastname'];

      $statusi = array(
        'priprema' => 'PR',
        'cekanje' => 'ČE',
        'poslan' => 'PO',
        'razduzen' => 'RA'
      );

      if($serviser) {
        if($serviser != $servisernal['ID']) {
          //
        } else {
          if ($dijelovi) {
            $ind = 0; 
            foreach ($dijelovi as $dio) {

              if($status) {
                if($status != $dio['status']) {
                  //
                } else {
                  $html .=  '<tr class="status-'.$dio['status'].'" id="dio-'.$nalog->ID.'-'. $ind . '">';
                  $html .=  '<td>' . get_field('redni_broj', $nalog->ID) . '</td>';
                  $html .=  '<td>' . get_field('model', $nalog->ID) . '</td>';
                  $html .=  '<td>' . get_field('serijski_broj', $nalog->ID). '</td>';
                  $html .=  '<td>' . get_field('ime', $nalog->ID) . ' ' . get_field('prezime', $nalog->ID). '</td>';
                  $html .=  '<td>' . get_field('grad', $nalog->ID) . '</td>';
                  $html .=  '<td>' . $servisertab . '</td>';
                  $html .=  '<td>' . $dio['opis'] . '</td>';
                  $html .=  '<td>' . $dio['vrijeme'] . '</td>';
                  $html .=  '<td><input maxlength="8" type="text" name="sifra_dijela" class="sifra_dijela" value="'.$dio['sifra'].'" data-nalog="'.$nalog->ID.'" data-index="'.$ind.'"></td>';
                  $html .=  '<td><input maxlength="8" type="number" name="komada_dijela" class="komada_dijela" value="'.$dio['komada'].'" data-nalog="'.$nalog->ID.'" data-index="'.$ind.'"></td>';
                  $html .=  '<td>';
                  $html .=  '<select name="status_dijela" class="status_dijela" data-nalog="'. $nalog->ID .'" data-index="'. $ind . '">';
                  foreach ($statusi as $statkey => $statval):
                  $html .=  '<option value="' . $statkey . '"';
                  if($dio['status'] == $statkey) { $html .= 'selected'; }
                  $html .= '>' . $statval . '</option>';
                  endforeach;
                  $html .=  '</select>';
                  $html .=  '</td>';
                  $html .=  '</tr>';

                  $ind++;
                }
              } else {
                $html .=  '<tr class="status-'.$dio['status'].'" id="dio-'.$nalog->ID.'-'. $ind . '">';
                $html .=  '<td>' . get_field('redni_broj', $nalog->ID) . '</td>';
                $html .=  '<td>' . get_field('model', $nalog->ID) . '</td>';
                $html .=  '<td>' . get_field('serijski_broj', $nalog->ID). '</td>';
                $html .=  '<td>' . get_field('ime', $nalog->ID) . ' ' . get_field('prezime', $nalog->ID). '</td>';
                $html .=  '<td>' . get_field('grad', $nalog->ID) . '</td>';
                $html .=  '<td>' . $servisertab . '</td>';
                $html .=  '<td>' . $dio['opis'] . '</td>';
                $html .=  '<td>' . $dio['vrijeme'] . '</td>';
                $html .=  '<td><input maxlength="8" type="text" name="sifra_dijela" class="sifra_dijela" value="'.$dio['sifra'].'" data-nalog="'.$nalog->ID.'" data-index="'.$ind.'"></td>';
                $html .=  '<td><input maxlength="8" type="number" name="komada_dijela" class="komada_dijela" value="'.$dio['komada'].'" data-nalog="'.$nalog->ID.'" data-index="'.$ind.'"></td>';
                $html .=  '<td>';
                $html .=  '<select name="status_dijela" class="status_dijela" data-nalog="'. $nalog->ID .'" data-index="'. $ind . '">';
                foreach ($statusi as $statkey => $statval):
                $html .=  '<option value="' . $statkey . '"';
                if($dio['status'] == $statkey) { $html .= 'selected'; }
                $html .= '>' . $statval . '</option>';
                endforeach;
                $html .=  '</select>';
                $html .=  '</td>';
                $html .=  '</tr>';

                $ind++;
              }
            }
          } 
        }
      } else {
        if ($dijelovi) {
          $ind = 0; 
          foreach ($dijelovi as $dio) {

            if($status) {
                if($status != $dio['status']) {
                  //
                } else {
                  $html .=  '<tr class="status-'.$dio['status'].'" id="dio-'.$nalog->ID.'-'. $ind . '">';
                  $html .=  '<td>' . get_field('redni_broj', $nalog->ID) . '</td>';
                  $html .=  '<td>' . get_field('model', $nalog->ID) . '</td>';
                  $html .=  '<td>' . get_field('serijski_broj', $nalog->ID). '</td>';
                  $html .=  '<td>' . get_field('ime', $nalog->ID) . ' ' . get_field('prezime', $nalog->ID). '</td>';
                  $html .=  '<td>' . get_field('grad', $nalog->ID) . '</td>';
                  $html .=  '<td>' . $servisertab . '</td>';
                  $html .=  '<td>' . $dio['opis'] . '</td>';
                  $html .=  '<td>' . $dio['vrijeme'] . '</td>';
                  $html .=  '<td><input maxlength="8" type="text" name="sifra_dijela" class="sifra_dijela" value="'.$dio['sifra'].'" data-nalog="'.$nalog->ID.'" data-index="'.$ind.'"></td>';
                  $html .=  '<td><input maxlength="8" type="number" name="komada_dijela" class="komada_dijela" value="'.$dio['komada'].'" data-nalog="'.$nalog->ID.'" data-index="'.$ind.'"></td>';
                  $html .=  '<td>';
                  $html .=  '<select name="status_dijela" class="status_dijela" data-nalog="'. $nalog->ID .'" data-index="'. $ind . '">';
                  foreach ($statusi as $statkey => $statval):
                  $html .=  '<option value="' . $statkey . '"';
                  if($dio['status'] == $statkey) { $html .= 'selected'; }
                  $html .= '>' . $statval . '</option>';
                  endforeach;
                  $html .=  '</select>';
                  $html .=  '</td>';
                  $html .=  '</tr>';

                  $ind++;
                }
              } else {
                $html .=  '<tr class="status-'.$dio['status'].'" id="dio-'.$nalog->ID.'-'. $ind . '">';
                $html .=  '<td>' . get_field('redni_broj', $nalog->ID) . '</td>';
                $html .=  '<td>' . get_field('model', $nalog->ID) . '</td>';
                $html .=  '<td>' . get_field('serijski_broj', $nalog->ID). '</td>';
                $html .=  '<td>' . get_field('ime', $nalog->ID) . ' ' . get_field('prezime', $nalog->ID). '</td>';
                $html .=  '<td>' . get_field('grad', $nalog->ID) . '</td>';
                $html .=  '<td>' . $servisertab . '</td>';
                $html .=  '<td>' . $dio['opis'] . '</td>';
                $html .=  '<td>' . $dio['vrijeme'] . '</td>';
                $html .=  '<td><input maxlength="8" type="text" name="sifra_dijela" class="sifra_dijela" value="'.$dio['sifra'].'" data-nalog="'.$nalog->ID.'" data-index="'.$ind.'"></td>';
                $html .=  '<td><input maxlength="8" type="number" name="komada_dijela" class="komada_dijela" value="'.$dio['komada'].'" data-nalog="'.$nalog->ID.'" data-index="'.$ind.'"></td>';
                $html .=  '<td>';
                $html .=  '<select name="status_dijela" class="status_dijela" data-nalog="'. $nalog->ID .'" data-index="'. $ind . '">';
                foreach ($statusi as $statkey => $statval):
                $html .=  '<option value="' . $statkey . '"';
                if($dio['status'] == $statkey) { $html .= 'selected'; }
                $html .= '>' . $statval . '</option>';
                endforeach;
                $html .=  '</select>';
                $html .=  '</td>';
                $html .=  '</tr>';

                $ind++;
              }

          }
        }  
      }

      
    }

    $html .= '</table>';
    
    echo $html;
    die;
}
 
/**
 * Disply callback for the Unsub page.
 */
 function rezervni_dijelovi_page_callback() { 

    $args = array(
      'post_type' => 'radni_nalog',
      'posts_per_page' => 1000,
      'orderby' => 'date',
      'order' => 'desc'
    );

    $nalozi = get_posts( $args );

    ?>
    <style>
      #rezervni {
        background: #fff;
        margin-top: 30px;
        padding: 15px;
        border-radius: 3px;
        width: 100%;
        height: 100vh;
      }

      #rezervni table th {
        font-size: 16px;
        text-align: left;
      }

      #rezervni .filteri {
        margin-bottom: 20px;
      }

      #rezervni .filteri {
        padding: 10px;
      }

      #rezervni .komada_dijela {
        max-width: 60px;
      }

      #rezervni .sifra_dijela {
        max-width: 120px;
      }

      #rezervni tr.status-priprema {
        background-color: #90EE90;
      }

      #rezervni tr.status-cekanje {
        background-color: #ADD8E6;
      }

      #rezervni tr.status-poslan {
        background-color: #FFCCCB;
      }
		
	  #rezervni tr.status-razduzen {
        background-color: #FFFFA7;
      }

    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
      jQuery(document).ready(function($) {
        $('body').on('change', '.sifra_dijela', function () {
          var nalog = $(this).attr('data-nalog');
          var index = $(this).attr('data-index');
          var sifra = $(this).val();

          $.ajax({
            type : "POST",
            dataType : "text",
            url : "/wp-admin/admin-ajax.php",
            data : {
             action: "change_rezervni",
             nalog : nalog,
             index : index,
             sifra : sifra
            },
            success: function(response) {
              //
            }
          });   
        });

        $('body').on('change', '.status_dijela', function () {
          var nalog = $(this).attr('data-nalog');
          var index = $(this).attr('data-index');
          var status = $(this).val();
          var dio = $('#dio-' + nalog + '-' + index);

          $.ajax({
            type : "POST",
            dataType : "text",
            url : "/wp-admin/admin-ajax.php",
            data : {
             action: "change_rezervni",
             nalog : nalog,
             index : index,
             status : status
            },
            success: function(response) {

               dio.removeClass();
               dio.addClass('status-' + response);
            }
          });   
        });

        $('body').on('change', '.komada_dijela', function () {
          var nalog = $(this).attr('data-nalog');
          var index = $(this).attr('data-index');
          var komada = $(this).val();

          $.ajax({
            type : "POST",
            dataType : "text",
            url : "/wp-admin/admin-ajax.php",
            data : {
             action: "change_rezervni",
             nalog : nalog,
             index : index,
             komada : komada
            },
            success: function(response) {
              //
            }
          });   
        });

        $('#filterrez').on('click', function () {
          var statusrez = $('#statusrez').val();
          var serviserrez = $('#serviserrez').val();

          $.ajax({
            type : "POST",
            dataType : "text",
            url : "/wp-admin/admin-ajax.php",
            data : {
             action: "filter_rezervni",
             serviserrez : serviserrez,
             statusrez : statusrez
            },
            success: function(response) {
              $('#tabeladijelova').html(response);
              console.log(response);
            }
          });   
        });
        $('#serviserrez').select2();
      });

    </script>
    <div id="rezervni">
      <div class="filteri">
        <?php 
        
          $args = array(
              'role'    => 'serviser',
              'orderby' => 'user_nicename',
              'order'   => 'ASC'
          );
          $serviseri = get_users( $args );
        ?>
        <label for="statusrez">Filtriraj po statusu</label>
        <select name="statusrez" id="statusrez">
          <option value="" selected>Odaberi status</option>
          <option value="priprema">U Priremi</option>
          <option value="cekanje">Na Čekanju</option>
          <option value="poslan">Poslan</option>
          <option value="razduzen">Razdužen</option>
        </select>

        <label for="serviserrez">Filtriraj po serviseru</label>
        <select name="serviserrez" id="serviserrez">
          <option value="" selected>Odaberi servisera</option>
          <?php foreach ($serviseri as $serviser): ?>
            <option value="<?php echo $serviser->ID; ?>"><?php echo $serviser->first_name . ' ' . $serviser->last_name; ?></option>
          <?php endforeach ?>
        </select>

        <button class="button button-secondary button-large" id="filterrez">Filtriraj</button>
      </div>

      <table id="tabeladijelova" cellpadding="5px" cellspacing="0" border="1px">
        <tr>
          <th>Broj Naloga</th>
          <th>Model</th>
          <th>Kataloški broj</th>
          <th>Ime stranke</th>
          <th>Grad</th>
          <th>Serviser</th>
          <th>Opis dijela</th>
          <th>Vrijeme</th>
          <th>Šifra dijela</th>
          <th>Komada</th>
          <th>Status</th>
        </tr>

        <?php 
        $dijeloviniz = array();

        $statusi = array(
          'priprema' => 'PR',
          'cekanje' => 'ČE',
          'poslan' => 'PO',
          'razduzen' => 'RA'
        );

        foreach ($nalozi as $nalog): 

          $i = 0;
          $dijelovi = get_field('dijelovi', $nalog->ID);

          $serviserobj = get_field('serviser', $nalog->ID);
          $serviser = $serviserobj['user_firstname'] . ' ' . $serviserobj['user_lastname'];

          if ($dijelovi):
            $ind = 0; 
          
            foreach ($dijelovi as $dio): 

              $dio['ind'] = $ind;
              $dio['nalog'] = $nalog->ID;
              $dio['serviser'] = $serviser;

              array_push($dijeloviniz, $dio);

              $ind++;

            endforeach;
          
          endif; 
        
        endforeach;
        function date_compare($a, $b)
        {
            $t1 = strtotime($a['vrijeme']);
            $t2 = strtotime($b['vrijeme']);

            return $t2 - $t1;
        }    

        usort($dijeloviniz, 'date_compare');
        
        foreach($dijeloviniz as $dio):
        
          ?>     
          <tr class="status-<?php echo $dio['status']; ?>" id="dio-<?php echo $dio['nalog']; ?>-<?php echo $dio['ind']; ?>">
            <td><?php echo get_field('redni_broj',  $dio['nalog']); ?></td>
            <td><?php echo get_field('model',  $dio['nalog']); ?></td>
            <td><?php echo get_field('serijski_broj',  $dio['nalog']); ?></td>
            <td><?php echo get_field('ime',  $dio['nalog']) . ' ' . get_field('prezime', $dio['nalog']); ?></td>
            <td><?php echo get_field('grad',  $dio['nalog']); ?></td>
            <td><?php echo $dio['serviser']; ?></td>
            <td><?php echo $dio['opis']; ?></td>
            <td><?php echo $dio['vrijeme']; ?></td>
            <td><input maxlength="8" type="text" name="sifra_dijela" class="sifra_dijela" value="<?php echo $dio['sifra']; ?>" data-nalog="<?php echo $dio['nalog']; ?>" data-index="<?php echo $dio['ind']; ?>"></td>
            <td><input maxlength="8" type="number" name="komada_dijela" class="komada_dijela" value="<?php echo $dio['komada']; ?>" data-nalog="<?php echo $dio['nalog']; ?>" data-index="<?php echo $dio['ind']; ?>"></td>
            <td>
              <select name="status_dijela" class="status_dijela" data-nalog="<?php echo $dio['nalog']; ?>" data-index="<?php echo $dio['ind']; ?>">
                <?php foreach ($statusi as $statkey => $statval): ?>
                  <option value="<?php echo $statkey; ?>" <?php echo ($dio['status'] == $statkey)?'selected':''; ?> ><?php echo $statval; ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <?php endforeach; ?>
        
      </table>
    </div>
 <?php } 


 /**
 * Disply callback for the Unsub page.
 */
 function napomene_page_callback() { 

    $args = array(
      'post_type' => 'radni_nalog',
      'posts_per_page' => '1000',
      'order' => 'desc'
    );

    $nalozi = get_posts( $args );

    ?>
    <style>
      #rezervni {
        background: #fff;
        margin-top: 30px;
        padding: 15px;
        border-radius: 3px;
        width: 100%;
        height: 100vh;
      }

      #rezervni table th {
        font-size: 16px;
        text-align: left;
      }

      #rezervni .filteri {
        margin-bottom: 20px;
      }

      #rezervni .filteri {
        padding: 10px;
      }

      #rezervni .komada_dijela {
        max-width: 60px;
      }

      #rezervni .sifra_dijela {
        max-width: 120px;
      }

      #rezervni tr.status-priprema {
        background-color: #90EE90;
      }

      #rezervni tr.status-cekanje {
        background-color: #ADD8E6;
      }

      #rezervni tr.status-poslan {
        background-color: #FFCCCB;
      }
    
    #rezervni tr.status-razduzen {
        background-color: #FFFFA7;
      }

    </style>

    <div id="rezervni">

      <table id="tabeladijelova" cellpadding="5px" cellspacing="0" border="1px">
        <tr>
          <th>Broj Naloga</th>
          <th>Model</th>
          <th>Kataloški broj</th>
          <th>Ime stranke</th>
          <th>Grad</th>
          <th>Serviser</th>
          <th>Napomena</th>
          <th>Vrijeme</th>
        </tr>

        <?php foreach ($nalozi as $nalog): 
          $i = 0;
          $dijelovi = get_field('napomena', $nalog->ID);

          $serviserobj = get_field('serviser', $nalog->ID);
          $serviser = $serviserobj['user_firstname'] . ' ' . $serviserobj['user_lastname'];


        ?>
          <?php if ($dijelovi): ?>
          <?php $ind = 0; foreach ($dijelovi as $dio): 
            
          ?>     
          <tr class="status-<?php echo $dio['status']; ?>" id="dio-<?php echo $nalog->ID; ?>-<?php echo $ind; ?>">
            <td><?php echo get_field('redni_broj', $nalog->ID); ?></td>
            <td><?php echo get_field('model', $nalog->ID); ?></td>
            <td><?php echo get_field('serijski_broj', $nalog->ID); ?></td>
            <td><?php echo get_field('ime', $nalog->ID) . ' ' . get_field('prezime', $nalog->ID); ?></td>
            <td><?php echo get_field('grad', $nalog->ID); ?></td>
            <td><?php echo $serviser; ?></td>
            <td><?php echo $dio['opis']; ?></td>
            <td><?php echo $dio['vrijeme']; ?></td>
           
          </tr>
          <?php $ind++; endforeach; ?>
          <?php endif; ?>

        <?php endforeach; ?>
        
      </table>
    </div>
 <?php } 

 function ps_acf_save_post( $post_id ) {
  
    if ( get_post_type( $post_id ) !== 'radni_nalog' ) return;

    $alreadysaved = get_post_meta( $post_id, 'alreadysaved', true );

    if(!$alreadysaved) {
      $trenutni_broj = get_field('field_61e53d42d8163', 'option');
      $godina = get_field('field_61e53d61d8164', 'option');

      $novi_broj = intval($trenutni_broj) + 1;
      $rbr = strval($novi_broj) . '-' . $godina;

      update_field('field_61e53d42d8163', $novi_broj, 'option');
      update_field('field_61e53e0f684e5', $rbr, $post_id);
      update_post_meta( $post_id, 'alreadysaved', true, '' );
      
    }
    
    $naslov = ucwords($_POST['acf']['field_61dec7953c1e1']) . ' ' . ucwords($_POST['acf']['field_61dec7953c219']) . ' - ' . $_POST['acf']['field_61dec7953c24f'];

    if(!$_POST['acf']['field_61dec7953c1e1'] && !$_POST['acf']['field_61dec7953c1e1']) {
      $uredajid = $_POST['acf']['field_61dfe1be15236'];
      $uredaj = get_post($uredajid);

      $naslov = ucwords($_POST['acf']['field_619f695f095a8']) . ' ' . ucwords($_POST['acf']['field_619f69e2095a9']) . ' - ' . $uredaj->post_title;
      update_field('ime', ucwords($_POST['acf']['field_619f695f095a8']), $post_id);
      update_field('prezime', ucwords($_POST['acf']['field_619f69e2095a9']), $post_id);
      update_field('adresa', ucwords($_POST['acf']['field_619f6a18095ac']), $post_id);
      update_field('grad', ucwords($_POST['acf']['field_619f6a1c095ad']), $post_id);
    } else {
      update_field('ime', ucwords($_POST['acf']['field_61dec7953c1e1']), $post_id);
      update_field('prezime', ucwords($_POST['acf']['field_61dec7953c219']), $post_id);
      update_field('adresa', ucwords($_POST['acf']['field_61dec7953c286']), $post_id);
      update_field('grad', ucwords($_POST['acf']['field_61dec7953c2bc']), $post_id);    
    }

    $acf = $_POST['acf'];

    unset($acf['field_61c2fa10e1492']);
    unset($acf['field_61c2fa10e14cc']);
    unset($acf['field_61c2fa10e1505']);

    $mycontent = implode(" | ", $acf);

    // Prevent Infinite Looping...
    remove_action( 'acf/save_post', 'my_acf_save_post' );


    // Grab Post Data from the Form
    $post = array(
        'ID'           => $post_id,
        'post_title'   => $naslov,
        'post_content' => $mycontent
    );

    // Update the Post
    wp_update_post( $post );

    $uredaj = $acf['field_61dfe1be15236'];

    if($uredaj) {
      $model = get_post_meta( $uredaj, 'model', true );
      $kataloski = get_post_meta( $uredaj, 'kataloski_broj', true );
      update_post_meta( $post_id, 'model', $model );
      update_post_meta( $post_id, 'serijski_broj', $kataloski );
    }

    // Continue save action
    add_action( 'acf/save_post', 'my_save_post' );

    // Set the Return URL in Case of 'new' Post
    $_POST['return'] = add_query_arg( 'updated', 'true', get_permalink( $post_id ) );
}
add_action( 'acf/save_post', 'ps_acf_save_post', 10, 1 );

function validate_phone_num($phone) {
  if(strpos($phone, '+387') || strpos($phone, '00387') || strpos($phone, '387')) {
    return true;
  } else {
    return false;
  }
}

function send_sms_notification( $post_id ) {
    $notifsent = get_post_meta( $post_id, 'notifsent', true );
    if(!$notifsent) {
      /*$trenutni_broj = get_field('field_61e53d42d8163', 'option');
      $godina = get_field('field_61e53d61d8164', 'option');
      $novi_broj = intval($trenutni_broj);
      $rbr = strval($novi_broj) . '-' . $godina;

      $user_phone = $_POST['acf']['field_619f6a13095ab'];
      
      if(!$user_phone) {
        $user_phone = $_POST['acf']['field_61dec7953c24f'];
      }*/

      $rbr = get_field('redni_broj', $post_id);
      $user_phone = get_field('broj_telefona', $post_id);
      $deviceid = get_field('uredaj', $post_id);
      $device = get_field('model', $deviceid) . ' - ' . get_field('kataloski_broj', $deviceid); 
      if(strpos(',', $user_phone)) {
        $phone_arr = explode(',', $user_phone);
        $phone = $phone_arr[0];
      } else {
        $phone = $user_phone;
      }
      $company_name = get_field('company_name', 'option');
      

      if($phone) {

        if(validate_phone_num($phone)) {
          
          $user_sms_text = "Poštovani,\nVaša prijava kvara za uređaj " . $device . " uspješno je zaprimljena, pod rednim brojem " . $rbr . ". Očekujte poziv servisera u najkraćem roku. Srdačan pozdrav!";
       
          ps_sendsms($user_sms_text, $phone, $company_name);
        
        } 
 
      }

      update_post_meta( $post_id, 'notifsent', true, '' );
    } 
}

add_action( 'acf/save_post', 'send_sms_notification', 16 );


function acf_uppercase_names( $value, $post_id, $field, $original ) {
    $value = ucwords( $value );
    return $value;
}

function acf_save_device( $value, $post_id, $field, $original ) {
    $model = get_post_meta( $value, 'model', true );
    $kataloski = get_post_meta( $value, 'kataloski_broj', true );
    update_post_meta( $post_id, 'model', $model );
    update_post_meta( $post_id, 'serijski_broj', $kataloski );
    return $value;
}

// Apply to all fields.
add_filter('acf/update_value/name=ime', 'acf_uppercase_names', 10, 4);
add_filter('acf/update_value/name=prezime', 'acf_uppercase_names', 10, 4);
add_filter('acf/update_value/name=grad', 'acf_uppercase_names', 10, 4);
add_filter('acf/update_value/name=adresa', 'acf_uppercase_names', 10, 4);
add_filter('acf/update_value/name=uredaj', 'acf_save_device', 10, 4);

add_action( 'post_submitbox_start', 'send_notification_button' );
function send_notification_button($post){
    if($post->post_type == 'radni_nalog' && $post->post_status == 'publish') {
    ?>
    <div id="sendnot">
        <input name="sendnotification" type="button" class="button-large button-primary" id="sendnotification" value="Pošalji Notifikaciju" data-nalog="<?php echo $post->ID; ?>" />
        <div id="poslana" style="margin: 10px 0; font-size: 14px; font-weight: bold;"></div>
    </div>
    <script>
      jQuery(document).ready(function($) {  
        $('#sendnotification').on('click', function () {
            var nalog = $(this).attr('data-nalog');

            console.log(nalog);

            $.ajax({
              type : "POST",
              dataType : "text",
              url : "/wp-admin/admin-ajax.php",
              data : {
               action: "send_update_notification",
               nalog : nalog,
              },
              success: function(response) {
                 console.log(response);
                 $('#sendnot #poslana').html(response);
              }
            });   
          });
      });
    </script>
    <?php }
}

function add_garancija() {
  if ('radni_nalog' === get_current_screen()->id) {
    $postid = get_the_ID();
    $datumkup = get_field('datum_kupovine', $postid);
    $datumkup = str_replace('/', '-', $datumkup);
    $datumkuptime = strtotime($datumkup);
    $datum = date(get_option('date_format'));
    $datumtime = strtotime($datum);
    $interval = ($datumtime - $datumkuptime) / 86400;
    if($interval < 1825) {
      ?>
      <div class="updated">
          <p> 
              
              <?php _e('Uređaj je u garanciji', 'my-text-domain'); ?>
          </p>
      </div>
    <?php
    } else {
      ?>
      <div class="error">
          <p> 
              
              <?php _e('Uređaj nije u garanciji', 'my-text-domain'); ?>
          </p>
      </div>
      <?php
    }
  }
} 
add_action('admin_notices', 'add_garancija');


function send_update_notification() {

        $post_id = $_POST['nalog'];
        $post = get_post($post_id);

        if($post->post_type == 'radni_nalog') {
          $post_edit_link = get_edit_post_link( $post->ID );
          $author_email = get_the_author_meta( 'user_email', $post->post_author );
          $email_subject = 'Ažuriran nalog - ' . $post->post_title;
          $headers = array('Content-Type: text/html; charset=UTF-8');
          $email_text = 'Ažuriran je nalog na proservis aplikaciji.<br><br>Nalog možete pregledati na linku <a href="' . $post_edit_link . '">' . $post->post_title . '</a>';
          wp_mail( $author_email, $email_subject, $email_text, $headers);

          $notarr = array(
            'post_title' => $post->post_title,
            'post_status' => 'publish',
            'post_type' => 'notifikacija'
          );
          
          $notifikacija = wp_insert_post( $notarr, false, true );
          $status_naloga = get_field( 'status', $post->ID );

          $serviser_id = get_post_meta( $post->ID, 'serviser', true );
          if(!$serviser_id){ 
            $serviser_id = $_POST['acf']['field_61c2fa10e1492']; 
          }
          $serviser = get_user_by('ID', $serviser_id);

          if($serviser) {
            $servisermail = $serviser->user_email;
            $servisermailtext = 'Radni nalog '. $post->post_title .' ažuriran na aplikaciji.<br><br>Lijep pozdrav,<br><br>';
            update_field('serviser', $serviser_id, $notifikacija);
            $msgbody = "Radni nalog ažuriran - " . $post->post_title . ".";
            $send = pn_send_push_notification($serviser_id, "Proservis", $msgbody, $post->ID);
            wp_mail( $servisermail, $email_subject, $servisermailtext, $headers);
          }

          update_field('radni_nalog', $post->ID, $notifikacija);
          update_field('ime_naloga', $post->post_title, $notifikacija);
          update_field('tip_notifikacije', 'azuriran_nalog', $notifikacija);
        }

        echo 'notifikacija poslana.';
        die;
      
}

add_action( 'wp_ajax_nopriv_send_update_notification', 'send_update_notification' );
add_action( 'wp_ajax_send_update_notification', 'send_update_notification' );

if( function_exists('acf_add_options_page') ) {
  acf_add_options_page(array(
    'page_title'  => 'Numeracija Naloga',
    'menu_title'  => 'Numeracija Naloga',
    'menu_slug'   => 'numeracija-naloga',
    'capability'  => 'edit_posts',
    'redirect'    => false
  ));
}

class mishaDateRange{

  function __construct(){
  
    // if you do not want to remove default "by month filter", remove/comment this line
    add_filter( 'months_dropdown_results', '__return_empty_array' );
    
    // include CSS/JS, in our case jQuery UI datepicker
    add_action( 'admin_enqueue_scripts', array( $this, 'jqueryui' ) );
    
    // HTML of the filter
    add_action( 'restrict_manage_posts', array( $this, 'form' ) );
    
    // the function that filters posts
    add_action( 'pre_get_posts', array( $this, 'filterquery' ) );
    
  }

  /*
   * Add jQuery UI CSS and the datepicker script
   * Everything else should be already included in /wp-admin/ like jquery, jquery-ui-core etc
   * If you use WooCommerce, you can skip this function completely
   */
  function jqueryui(){
    wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
  }

  /*
   * Two input fields with CSS/JS
   * If you would like to move CSS and JavaScript to the external file - welcome.
   */
  function form(){
    
    $from = ( isset( $_GET['mishaDateFrom'] ) && $_GET['mishaDateFrom'] ) ? $_GET['mishaDateFrom'] : '';
    $to = ( isset( $_GET['mishaDateTo'] ) && $_GET['mishaDateTo'] ) ? $_GET['mishaDateTo'] : '';
    
    echo '<style>
    input[name="mishaDateFrom"], input[name="mishaDateTo"]{
      line-height: 28px;
      height: 28px;
      margin: 0;
      width:125px;
    }
    </style>
    
    <input type="text" name="mishaDateFrom" placeholder="Od Datuma" value="' . esc_attr( $from ) . '" />
    <input type="text" name="mishaDateTo" placeholder="Do Datuma" value="' . esc_attr( $to ) . '" />
  
    <script>
    jQuery( function($) {
      var from = $(\'input[name="mishaDateFrom"]\'),
          to = $(\'input[name="mishaDateTo"]\');

      $( \'input[name="mishaDateFrom"], input[name="mishaDateTo"]\' ).datepicker( {dateFormat : "yy-mm-dd"} );
      // by default, the dates look like this "April 3, 2017"
          // I decided to make it 2017-04-03 with this parameter datepicker({dateFormat : "yy-mm-dd"});
    
    
          // the rest part of the script prevents from choosing incorrect date interval
          from.on( \'change\', function() {
        to.datepicker( \'option\', \'minDate\', from.val() );
      });
        
      to.on( \'change\', function() {
        from.datepicker( \'option\', \'maxDate\', to.val() );
      });
      
    });
    </script>';
    
  }
  
  /*
   * The main function that actually filters the posts
   */
  function filterquery( $admin_query ){
    global $pagenow;
    
    if (
      is_admin()
      && $admin_query->is_main_query()
      // by default filter will be added to all post types, you can operate with $_GET['post_type'] to restrict it for some types
      && in_array( $pagenow, array( 'edit.php', 'upload.php' ) )
      && ( ! empty( $_GET['mishaDateFrom'] ) || ! empty( $_GET['mishaDateTo'] ) )
    ) {

      $admin_query->set(
        'date_query', // I love date_query appeared in WordPress 3.7!
        array(
          'after' => sanitize_text_field( $_GET['mishaDateFrom'] ), // any strtotime()-acceptable format!
          'before' => sanitize_text_field( $_GET['mishaDateTo'] ),
          'inclusive' => true, // include the selected days as well
          'column'    => 'post_date' // 'post_modified', 'post_date_gmt', 'post_modified_gmt'
        )
      );
      
    }
    
    return $admin_query;
  
  }

}
new mishaDateRange();


add_action( 'check_azuriran_nalog', 'check_azuriran_nalog_fun' );
function check_azuriran_nalog_fun() {
  $nalozi = get_posts(
    array(
      'post_type' => 'radni_nalog',
      'posts_per_page' => -1,
      'status' => 'publish'
    )
  );

  foreach ($nalozi as $nalog) {

    $status = get_field('status', $nalog->ID);
    
    if($status == 'zatvoren' || $status == 'skica' || $status == 'zamjena' || $status == 'zamjenako') {
      //
    } else {
  
      $notifikacije = get_posts(
        array(
          'post_type' => 'notifikacija',
          'posts_per_page' => -1,
          'meta_query' => array(
            array(
              'key' => 'radni_nalog',
              'value' => $nalog->ID,
              'compare' => '='
            )
          )
        )
      );

      foreach ($notifikacije as $notifikacija) {
        $type = get_field('tip_notifikacije', $notifikacija->ID);

        if($type == 'promjena_statusa') {
          $timenot = get_the_date('Y-m-d', $notifikacija->ID);
          $time = date('Y-m-d');
          $timechk = date('Y-m-d', strtotime($time . ' -3 days'));

          if ($timenot < $timechk) {

            $post_edit_link = get_edit_post_link( $nalog->ID );
            $author_email = get_the_author_meta( 'user_email', $nalog->post_author );
            $email_subject = 'Radni nalog nije ažuiran duže od 3 dana - ' . $nalog->post_title;
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $email_text = 'Radni nalog '. $nalog->post_title .' nije ažuriran duže od 3 dana na  aplikaciji.<br><br>Nalog možete pregledati na linku <a href="' . $post_edit_link . '">' . $nalog->post_title . '</a>';
            wp_mail( $author_email, $email_subject, $email_text, $headers);

            $notarr = array(
              'post_title' => $nalog->post_title,
              'post_status' => 'publish',
              'post_type' => 'notifikacija'
            );
            
            $notifikacijanew = wp_insert_post( $notarr, false, true );
            $status_naloga = get_field( 'status', $nalog->ID );

            $serviser_id = get_post_meta( $nalog->ID, 'serviser', true );
            $serviser = get_user_by('ID', $serviser_id);

            if($serviser) {
              $servisermail = $serviser->user_email;
              
              //$servisermailtext = 'Radni nalog '. $post->post_title .' nije ažuriran duže od 3 dana na  aplikaciji.<br><br>Lijep pozdrav,<br><br>';
              $servisermailtext = 'Radni nalog ' . $nalog->post_title . ' nije riješen duži vremenski period. Molimo da ga riješite u što kraćem roku!';
              update_field('serviser', $serviser_id, $notifikacija);
              //$msgbody = "Radni nalog nije ažuiran duže od 3 dana - " . $nalog->post_title . ".";
              $send = pn_send_push_notification($serviser_id, "Proservis", $servisermailtext, $post->ID);
              wp_mail( $servisermail, $email_subject, $servisermailtext, $headers);
            }

            update_field('radni_nalog', $nalog->ID, $notifikacijanew);
            update_field('ime_naloga', $nalog->post_title, $notifikacijanew);
            update_field('tip_notifikacije', 'nalog_nije_azuriran', $notifikacijanew);
          }
        } 
      }
    }
  }
}

add_filter( 'bulk_actions-edit-radni_nalog', 'register_my_bulk_actions' );
 
function register_my_bulk_actions($bulk_actions) {
  $bulk_actions['send_bulk_notification'] = __( 'Pošalji notifikaciju', 'send_bulk_notification');
  return $bulk_actions;
}

add_filter( 'handle_bulk_actions-edit-radni_nalog', 'my_bulk_action_handler', 10, 3 );
 
function my_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
  if ( $doaction !== 'send_bulk_notification' ) {
    return $redirect_to;
  }

  foreach ( $post_ids as $post_id ) {
        $post = get_post($post_id);

        if($post->post_type == 'radni_nalog') {
          $post_edit_link = get_edit_post_link( $post->ID );
          $author_email = get_the_author_meta( 'user_email', $post->post_author );
          $email_subject = 'Ažuriran nalog - ' . $post->post_title;
          $headers = array('Content-Type: text/html; charset=UTF-8');
          $email_text = 'Ažuriran je nalog na proservis aplikaciji.<br><br>Nalog možete pregledati na linku <a href="' . $post_edit_link . '">' . $post->post_title . '</a>';
          wp_mail( $author_email, $email_subject, $email_text, $headers);

          $notarr = array(
            'post_title' => $post->post_title,
            'post_status' => 'publish',
            'post_type' => 'notifikacija'
          );
          
          $notifikacija = wp_insert_post( $notarr, false, true );
          $status_naloga = get_field( 'status', $post->ID );

          $serviser_id = get_post_meta( $post->ID, 'serviser', true );
          if(!$serviser_id){ 
            $serviser_id = $_POST['acf']['field_61c2fa10e1492']; 
          }
          $serviser = get_user_by('ID', $serviser_id);

          if($serviser) {
            $servisermail = $serviser->user_email;
            $servisermailtext = 'Radni nalog '. $post->post_title .' ažuriran na  aplikaciji.<br><br>Lijep pozdrav,<br><br>';
            update_field('serviser', $serviser_id, $notifikacija);
            $msgbody = "Radni nalog ažuriran - " . $post->post_title . ".";
            $send = pn_send_push_notification($serviser_id, "Proservis", $msgbody, $post->ID);
            wp_mail( $servisermail, $email_subject, $servisermailtext, $headers);
          }

          update_field('radni_nalog', $post->ID, $notifikacija);
          update_field('ime_naloga', $post->post_title, $notifikacija);
          update_field('tip_notifikacije', 'azuriran_nalog', $notifikacija);
        }
  }

  $redirect_to = add_query_arg( 'sent_bulk_notifications', count( $post_ids ), $redirect_to );
  return $redirect_to;
}

add_action( 'admin_notices', 'my_bulk_action_admin_notice' );
 
function my_bulk_action_admin_notice() {
  if ( ! empty( $_REQUEST['sent_bulk_notifications'] ) ) {
    $emailed_count = intval( $_REQUEST['sent_bulk_notifications'] );
    printf( '<div id="message" class="updated fade">' .
      _n( 'Poslane %s notifikacije.',
        'Poslane %s notifikacije',
        $emailed_count,
        'send_bulk_notification'
      ) . '</div>', $emailed_count );
  }
}

function view_order_log() {
  global $post;

  $args = array(
    'post_type' => 'notifikacija',
    'posts_per_page' => -1,
    'meta_query' => array(
      array(
        'key' => 'radni_nalog',
        'value' => $post->ID,
        'compare' => '='
      )
    )
  );

  $notifikacije = get_posts($args);
  $i = 1;
  echo '<table class="order-log-table" cellpadding="5" cellspacing="5"><tr>';
  echo '<th class="order-log-item"><div class="order-log-item-num" style="font-weight: bold; ">Rbr</th>';
  echo '<th class="order-log-item-date" style="font-weight: bold;">Datum</th>';
  echo '<th class="order-log-item-text-title" style="font-weight: bold;">Promjena</th>';
  echo '</tr>';
  $notifikacije = array_reverse($notifikacije);
  foreach($notifikacije as $notifikacija) {
    $tip_notifikacije = get_field('tip_notifikacije', $notifikacija->ID);
    $datum = get_the_date('d.m.Y H:i', $notifikacija->ID);
    $novi_status = get_field('novi_status', $notifikacija->ID);
    $novi_status_rez = get_field('novi_status_rez', $notifikacija->ID);

    if($tip_notifikacije == 'azuriran_nalog') {
      $tip = 'Nalog ažuriran';
    } else if($tip_notifikacije == 'nalog_nije_azuriran') {
      $tip = 'Poslana notifikacija da nalog nije ažuriran';
    } else if($tip_notifikacije == 'novi_nalog') {
      $tip = 'Nalog kreiran';
    } else if($tip_notifikacije == 'promjena_statusa') {
      $tip = 'Status naloga promijenjen u ' . $novi_status;
    } else if($tip_notifikacije == 'napomena') {
      $tip = 'Dodana napomena na nalogu';
    } else if($tip_notifikacije == 'rezervni')  {
      $tip = 'Zatražen rezervni dio';
    } else if($tip_notifikacije == 'rezervni_status')  {
      $tip = 'Promjenjen status rezervnog dijela u ' . $novi_status_rez;
    }
     
    echo '<tr>';
    echo '<td class="order-log-item"><div class="order-log-item-num" style="margin-right: 10px; font-weight: bold; ">' . $i .'.</td>';
    echo '<td class="order-log-item-date" style="margin-right: 10px;">' . $datum . '</td>';
    echo '<td class="order-log-item-text-title" style="margin-left: 10px;">' . $tip . '</td>';
    echo '</tr>';
    $i++;
  }
  echo '</table>';
}



/**
 * Register meta boxes.
 */
function hcf_register_meta_boxes() {
  add_meta_box( 'order-history-box', __( 'Historija naloga', 'hcf' ), 'view_order_log', 'radni_nalog' );
}
add_action( 'add_meta_boxes', 'hcf_register_meta_boxes' );


add_action( 'post_submitbox_start', 'mpdf_button' );

function mpdf_button($post) {
    if($post->post_type == 'radni_nalog' && $post->post_status == 'publish') {
    ?>
    <div id="sendnot">
      <button id="mpdfbtn" data-nalog="<?php echo $post->ID; ?>" type="button" class="button-large button-primary">Printaj nalog</button>
    </div>
    <script>
      jQuery(document).ready(function($) {  
        $('#mpdfbtn').on('click', function () {
            var nalog = $(this).attr('data-nalog');

            console.log(nalog);

            $.ajax({
              type : "POST",
              dataType : "text",
              url : "/wp-admin/admin-ajax.php",
              data : {
               action: "download_pdf",
               nalog : nalog,
              },
              success: function(response) {
                 console.log(response);
                 window.open(response, '_blank');
              }
            });   
          });
      });
    </script>
    <?php }
}

function download_pdf() {

  $nalog = $_POST['nalog'];
  $br_naloga = get_field('redni_broj', $nalog);
  $fields = get_fields($nalog);
  $datumprijave = get_the_date('d.m.Y.', $nalog);
  $fieldkup = (string) $fields['datum_kupovine'];
  $datumkup = str_replace('/','.',$fieldkup) . '.';
  $serviser = get_field('serviser', $nalog);
  $serviserime = $serviser['user_firstname'] . ' ' . $serviser['user_lastname'];
  $author_id = get_post_field( 'post_author', $nalog ); 
  $author_name = get_the_author_meta( 'display_name', $author_id );


  require_once (get_stylesheet_directory() . '/vendor/autoload.php');

  $memo = get_stylesheet_directory_uri() . '/pdf/assets/memorandum.jpg';
  
  $html = '<div class="memorandum" style="margin-bottom: 20px; width: 100%; display: block;"><img style="width: 100%; height: auto;" src="'.$memo.'"></div>';
  $html .= '<table style="width: 100%; border-collapse: collapse; border-spacing: 0; margin-bottom: 20px; "><tr>';
  $html .= '<td><h1 style="display: block; font-size: 17px; font-weight: 400; padding-bottom: 5px; color: #000;">Izvještaj o radnom nalogu broj <strong>'.$br_naloga.'</strong> na dan '.date('d.m.Y.').'</h1></td>';
  $html .= '<td><h2 style="display: block; font-size: 15px; font-weight: 400; padding-bottom: 5px; color: #000;">Status: <strong>'.$fields['status'] .'</strong></h2></td>';
  $html .= '</tr></table>';
  $html .= '<h2 style="display: block; font-size: 15px; font-weight: 400; padding-bottom: 5px; margin-top: 20px; margin-bottom: 20px; border-bottom: 1px solid #ac1600; color: #000;">Informacije o Kupcu</h2>';
  $html .= '<table style="width: 100%; border-collapse: collapse; border-spacing: 0; margin-bottom: 20px;">';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Ime i prezime</td><td style="font-weight: bold;font-size: 12px;">'.$fields['ime'].' '.$fields['prezime'].'</td></tr>';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Broj Telefona</td><td style="font-weight: bold;font-size: 12px;">'.$fields['broj_telefona'].'</td></tr>';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Email adresa</td><td style="font-weight: bold;font-size: 12px;">'.$fields['email_adresa'].'</td></tr>';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Adresa</td><td style="font-weight: bold;font-size: 12px;">'.$fields['adresa'].'</td></tr>';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Grad</td><td style="font-weight: bold;font-size: 12px;">'.$fields['grad'].'</td></tr>';
  $html .= '</table>';
  $html .= '<h2 style="display: block; font-size: 15px; font-weight: 400; padding-bottom: 5px; margin-top: 20px; margin-bottom: 20px; border-bottom: 1px solid #ac1600; color: #000;">Informacije o Uređaju</h2>';
  $html .= '<table style="width: 100%; border-collapse: collapse; border-spacing: 0; margin-bottom: 20px;">';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Model</td><td style="font-weight: bold;font-size: 12px;">'.$fields['model'].'</td></tr>';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Kataloški broj</td><td style="font-weight: bold;font-size: 12px;">'.$fields['serijski_broj'].'</td></tr>';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Datum Kupovine</td><td style="font-weight: bold;font-size: 12px;">'.$datumkup.'</td></tr>';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Datum Prijave</td><td style="font-weight: bold;font-size: 12px;">'.$datumprijave.'</td></tr>';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Mjesto</td><td style="font-weight: bold;font-size: 12px;">'.$fields['mjesto_kupovine'].'</td></tr>';
  $html .= '<tr><td style="font-size: 12px; font-weight: normal; width: 170px;">Opis Kvara</td><td style="font-weight: bold;font-size: 12px;">'.$fields['opis_kvara'].'</td></tr>';
  $html .= '</table>';
  $html .= '<h2 style="display: block; font-size: 15px; font-weight: 400; padding-bottom: 5px; margin-top: 20px; margin-bottom: 20px; border-bottom: 1px solid #ac1600; color: #000;">Informacije o Servisu</h2>';
  $html .= '<table style="width: 100%; border-collapse: collapse; border-spacing: 0; ">';
  $html .= '<tr><td style="width: 400px; text-align: left; vertical-align: top;">';
  $html .= '<table style="width: 100%; border-collapse: collapse; border-spacing: 0;">';
  $html .= '<tr><td style="font-size: 13px; font-weight: normal; width: 170px;">Serviser</td><td style="font-weight: bold;font-size: 13px;">'.$serviserime.'</td></tr>';
  $html .= '</table>';
  
  if(count($fields['napomena']) >= 1) {
    $html .= '<table style="width: 100%; border-collapse: collapse; border-spacing: 0; ">';
    $html .= '<tr><td style="font-size: 13px; font-weight: bold; padding-top: 10px; padding-bottom: 5px;">Napomene</td><td style="font-size: 13px; font-weight: bold; padding-top: 10px; padding-bottom: 5px;">Kreirano</td></tr>';
    foreach ($fields['napomena'] as $napomena) {
      $html .= '<tr><td style="font-weight: normal;font-size: 12px;">'.$napomena['opis'].'</td><td style="font-weight: normal;font-size: 12px;">'.$napomena['vrijeme'].'</td></tr>';
    }
    $html .= '</table>';
  }
  if(count($fields['dijelovi']) >= 1) {
    $html .= '<table style="width: 100%; border-collapse: collapse; border-spacing: 0; margin-top: 20px; padding-top: 5px; border-top: 1px solid #000;">';
    $html .= '<tr style="display: block; margin-top: 20px;"><td style="font-size: 13px; font-weight: bold; padding-top: 10px; padding-bottom: 5px;">Rezervni dijelovi</td><td style="font-size: 13px; margin-top: 10px; padding-top: 10px; font-weight: bold; padding-top: 10px; padding-bottom: 5px;">Naručeno</td><td style="font-size: 13px; padding-top: 10px; font-weight: bold; padding-bottom: 5px;">Status</td></tr>';
    foreach ($fields['dijelovi'] as $rezervni) {
      $html .= '<tr><td style="font-weight: normal;font-size: 12px;">'.$rezervni['opis'].'</td><td style="font-weight: normal;font-size: 12px;">'.$rezervni['vrijeme'].'</td><td style="font-weight: normal;font-size: 12px;">'.$rezervni['status'].'</td></tr>';
    }
    $html .= '</table>';
  }
  $html .= '</td>';

  $args = array(
    'post_type' => 'notifikacija',
    'posts_per_page' => -1,
    'meta_query' => array(
      array(
        'key' => 'radni_nalog',
        'value' => $nalog,
        'compare' => '='
      )
    )
  );

  $notifikacije = get_posts($args);

  $j = 1;
  $html .= '<td style="width: 400px; text-align: left; border-left: 1px solid #000; vertical-align: top; padding-left: 20px;">';
  $html .= '<h3 style="font-size: 14px; display: block; font-weight: bold; margin-bottom: 10px; margin-left: 2px; color: #000;">Istorija promjena u nalogu</h2>';
  $html .= '<table class="order-log-table" style="margin-top: 10px;">';
  $notifikacije = array_reverse($notifikacije);
  foreach($notifikacije as $notifikacija) {
    $tip_notifikacije = get_field('tip_notifikacije', $notifikacija->ID);
    $datum = get_the_date('d.m.Y H:i', $notifikacija->ID);
    $novi_status = get_field('novi_status', $notifikacija->ID);
    $novi_status_rez = get_field('novi_status_rez', $notifikacija->ID);

    if($tip_notifikacije == 'azuriran_nalog') {
      $tip = 'Nalog ažuriran';
    } else if($tip_notifikacije == 'nalog_nije_azuriran') {
      $tip = 'Poslana notifikacija da nalog nije ažuriran';
    } else if($tip_notifikacije == 'novi_nalog') {
      $tip = 'Nalog kreiran';
    } else if($tip_notifikacije == 'promjena_statusa') {
      $tip = 'Status naloga promijenjen u ' . $novi_status;
    } else if($tip_notifikacije == 'napomena') {
      $tip = 'Dodana napomena na nalogu';
    } else if($tip_notifikacije == 'rezervni')  {
      $tip = 'Zatražen rezervni dio';
    } else if($tip_notifikacije == 'rezervni_status')  {
      $tip = 'Promjenjen status rezervnog dijela u ' . $novi_status_rez;
    }
     
    $html .= '<tr>';
    $html .= '<td class="order-log-item"><div class="order-log-item-num" style="font-weight: bold; font-size: 12px;">' . $j .'.</td>';
    $html .= '<td class="order-log-item-date" style="font-size: 12px;">' . $datum . '</td>';
    $html .= '<td class="order-log-item-text-title" style="font-size: 12px;">' . $tip . '</td>';
    $html .= '</tr>';
    $j++;
  }
  $html .= '</table>';
  $html .= '</td>';
  $html .= '</tr>';
  $html .= '</table>';

  $html .= '<div style="width: 100%; position: absolute; display: block; width: 780px; left: 50px; bottom: 80px; top: auto; right: 50px;">';
  $html .= '<table style="width: 100%;">';
  $html .= '<tr>';
  $html .= '<td style="font-size: 13px; font-weight: normal; width: 260px;">Izradio/zaprimio</td>';
  $html .= '<td style="font-size: 13px; font-weight: normal; width: 260px;">Odgovorna osoba</td>';
  $html .= '<td style="font-size: 13px; font-weight: normal; width: 260px;">Serviser</td>';
  $html .= '</tr>';
  $html .= '<tr>';
  $html .= '<td style="font-size: 13px; font-weight: bold; width: 200px;">'.$author_name.'</td>';
  $html .= '<td style="font-size: 13px; font-weight: bold; width: 200px;">Romana Milanković</td>';
  $html .= '<td style="font-size: 13px; font-weight: bold; width: 200px;">'.$serviserime.'</td>';
  $html .= '</tr>';
  $html .= '</table>';
  $html .= '</div>';

  $mpdf = new \Mpdf\Mpdf();
  $mpdf->WriteHTML($html);
  $filename = get_stylesheet_directory() . '/pdf/nalozi/nalog-'.$br_naloga.'.pdf';
  $fileurl = get_stylesheet_directory_uri() . '/pdf/nalozi/nalog-'.$br_naloga.'.pdf';
  $mpdf->Output($filename,'F');
  
  echo $fileurl;
  exit();

}

add_action('wp_ajax_download_pdf', 'download_pdf');
add_action('wp_ajax_nopriv_download_pdf', 'download_pdf');

function ps_sendsms($message, $recipient, $companyname) {
  require_once(get_stylesheet_directory() . '/vendor/mailjetapi.php');

  //create a new instance
  $apikey = get_field('mailjet_api_key', 'option');
  $oApi = new Mailjetsms($apikey);

  //Send an SMS
  $aData = array(
    'from' => $companyname,
    'to' => $recipient,
    'text' => $message
  );

  $oResponse = $oApi->send($aData);

  if( $oResponse->success ){
    //printf('Message %s is on the way to %s.', $oResponse->MessageId, $oResponse->To);
  } else{
    //var_dump($oResponse);
  }
}

add_action( 'dp_duplicate_post', 'set_rbr_on_copy', 10, 3 );

function set_rbr_on_copy( $new_post_id, $post, $status ) {
  // Get the original post's type.
  $post_type = $post->post_type;

  // If this is a 'post' post, make sure to update the post's slug.
  if ( 'radni_nalog' == $post_type ) {
      $trenutni_broj = get_field('field_61e53d42d8163', 'option');
      $godina = get_field('field_61e53d61d8164', 'option');

      $novi_broj = intval($trenutni_broj) + 1;
      $rbr = strval($novi_broj) . '-' . $godina;

      update_field('field_61e53d42d8163', $novi_broj, 'option');
      update_field('field_61e53e0f684e5', $rbr, $new_post_id);
  } else {
    // Do nothing.
  }

}


/********* Export to csv ***********/
add_action('admin_footer', 'mytheme_export_users');

function mytheme_export_users() {
    $screen = get_current_screen();
    if ( $screen->post_type != "radni_nalog" )  
        return;
    ?>
    <script type="text/javascript">
        jQuery(document).ready( function($)
        {
            $('.tablenav.top .clear, .tablenav.bottom .clear').before('<form action="#" method="POST"><input type="hidden" id="mytheme_export_csv" name="mytheme_export_csv" value="1" /><input class="button button-primary user_export_button" style="margin-top:3px;" type="submit" value="<?php esc_attr_e('Izvoz u Excel', 'mytheme');?>" /></form>');
        });
    </script>
    <?php
}

add_action('admin_init', 'export_csv'); //you can use admin_init as well

function export_csv() {
    if (!empty($_POST['mytheme_export_csv'])) {

        if (current_user_can('edit_posts')) {

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="Nalozi '.date('YmdHis').'.csv"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM

            $args = array (
                'order'          => 'DESC',
                'orderby'        => 'date',
                'posts_per_page' => -1,
				        'post_type' => 'radni_nalog'
            );

            $nalozi = get_posts( $args );
			echo '"Br. Naloga";"Ime i prezime kupca";"Telefon kupca";"Naziv Uređaja";"Serviser";"Status Naloga";"Datum Kupovine";"Datum Prijave"' . "\r\n";
            foreach ( $nalozi as $nalog ) {
        $rbr = get_field('redni_broj', $nalog->ID);
				$ime = get_field('ime', $nalog->ID) . ' ' . get_field('prezime', $nalog->ID);
        $telefon = get_field('broj_telefona', $nalog->ID);
        $uredaj_id = get_field('uredaj', $nalog->ID);
        $ured = get_post($uredaj_id);
        $uredaj = $ured->post_title;
        $status = get_field('status', $nalog->ID);
        $serviser = get_field('serviser', $nalog->ID);
        $serviserd = get_userdata($serviser['ID']);
        $servisern = $serviserd->display_name;
        $datumkupo = get_field('datum_kupovine', $nalog->ID);
        $datumkup = date("d.m.Y.", strtotime($datumkupo));  
        $datumprij = $nalog->post_date;
				
          echo '"' . $rbr . '";"' . $ime . '";"' . $telefon . '";"' . $uredaj . '";"' . $servisern . '";"' . $status . '";"' . $datumkup . '";"' . $datumprij . '" ' . "\r\n";
        }

            exit();
        }
    }
}