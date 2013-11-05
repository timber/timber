<?php

class TimberComment extends TimberCore {

    var $PostClass = 'TimberPost';

    public static $representation = 'comment';

    function __construct($cid) {
        $this->init($cid);
    }

    /* core definition */

    function author() {
        if ($this->user_id) {
            return new TimberUser($this->user_id);
        }
        $fakeUser = new stdClass();
        $fakeUser->name = 'Anonymous';
        if ($this->comment_author) {
            $fakeUser->name = $this->comment_author;
        }
        return $fakeUser;
    }

    function date() {
        return $this->comment_date;
    }

    function content() {
        return $this->comment_content;
    }

    function init($cid) {
        $comment_data = $cid;
        if (is_integer($cid)) {
            $comment_data = get_comment($cid);
        }
        $this->import($comment_data);
        $this->ID = $this->comment_ID;
    }
    
    
    public function avatar($size=92, $default='<path_to_url>'){
      // Fetches the Gravatar
      // use it like this
      // {{comment.avatar(36,template_uri~"/img/dude.jpg")}}
    
      if ( ! get_option('show_avatars') ) return false;       
      if ( !is_numeric($size) ) $size = '92';
  
      $email = $this->avatar_email();
      if ( !empty($email) ) $email_hash = md5( strtolower( trim( $email ) ) );
      
      $host = $this->avatar_host($email_hash);
              
      $default = $this->avatar_default($default,$email, $size, $host);         
        
      if( !empty($email) ) {
        $avatar = $this->avatar_out($email, $default, $host, $email_hash, $size);
      }else{
        $avatar = $default;
      }
      
      return $avatar;      
    }


    private function avatar_email(){
        $email = '';           
        $id = (int) $this->user_id;
        $user = get_userdata($this->ID);
        if($user){
         $email = $user->user_email; 
        }else{
          $email = $this->comment_author_email;
        }
        return $email;
    }

    private function avatar_host($email_hash){
        if ( is_ssl() ) {
            $host = 'https://secure.gravatar.com';
        } else {
            if ( !empty($email) ){
                $host = sprintf( "http://%d.gravatar.com", ( hexdec( $email_hash[0] ) % 2 ) );
            }else{
                $host = 'http://0.gravatar.com';
            }                        
        }
        return $host;       
    }
    
    private function avatar_default($default,$email, $size, $host){
       # what if its relative.       
       if(substr ( $default , 0, 1 ) == "/" ){
        $default = get_template_directory_uri() . $default;  
       }
       
       
      if (empty($default) ){
        $avatar_default = get_option('avatar_default');
        if ( empty($avatar_default) ){
            $default = 'mystery';        
        }else{
            $default = $avatar_default;           
        } 
      }
    
      if ( 'mystery' == $default )
          $default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}"; 
          // ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
      elseif ( 'blank' == $default )
          $default = $email ? 'blank' : includes_url( 'images/blank.gif' );
      elseif ( !empty($email) && 'gravatar_default' == $default )
          $default = '';
      elseif ( 'gravatar_default' == $default )
          $default = "$host/avatar/?s={$size}";
      elseif ( empty($email) )
          $default = "$host/avatar/?d=$default&amp;s={$size}";
      elseif ( strpos($default, 'http://') === 0 )
          $default = add_query_arg( 's', $size, $default );
     
     return $default;         
    }
    
    private function avatar_out($email, $default, $host, $email_hash, $size){
        $out = "$host/avatar/";
        $out .= $email_hash;
        $out .= '?s='.$size;
        $out .= '&amp;d=' . urlencode( $default );
        
        $rating = get_option('avatar_rating');
        if ( !empty( $rating ) ) $out .= "&amp;r={$rating}";
        
        return  str_replace( '&#038;', '&amp;', esc_url( $out ) );          
    }

    
}
