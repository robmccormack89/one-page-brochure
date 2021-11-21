<?php
namespace Rmcc;
use PHPMailer\PHPMailer\PHPMailer;

class SingleController extends CoreController {
  
  /*
  *
  * This class is used to render singular objects like pages, posts, projects etc.
  * Create a new SingleController object with $type & $slug properties.
  * Call the getSingle() method on the object to render it.
  * This is mainly for use within a routing context, see config/routes.
  *
  */
  public function __construct(string $type, string $slug) {
    parent::__construct();
    global $_context;
    global $config;
    $this->type = $type; // e.g 'page' or 'blog' or 'portfolio'
    $this->slug = $slug; // e.g 'about'. this will usually come from the request unless setting for specific pages
    // the $name property is only used for render() to differenciate between archived & non-archived singular objects
    $this->name = ($this->type == 'page') ? $this->type : $config['types'][$this->type]['single'];
    $q = new Json($config['json_secret']);
    $this->secret = $q->fetch();
    $this->init();
  }
  
  private function init() {
    global $_context;
    $_context = array(
      'single' => 'Single',
      'type' => $this->type,
      'slug' => $this->slug,
      'name' => $this->name,
    );
  }
  
  public function getContact() {
    global $_context;
    if (array_key_exists('name', $_POST) && array_key_exists('email', $_POST)) {
      
      // ajax defaults
      date_default_timezone_set('Etc/UTC');
      $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  
      // form fields defaults
      $err = false;
      
      // form fields & validations
      if (array_key_exists('name', $_POST) && !empty($_POST['name'])) {
        $name = substr(strip_tags($_POST['name']), 0, 255);
      } else {
        $display_msg = 'Error: No name provided';
        $err = true;
      }
      if (array_key_exists('email', $_POST) && PHPMailer::validateAddress($_POST['email'])) {
        $email = $_POST['email'];
      } else {
        if (!array_key_exists('name', $_POST) || empty($_POST['name'])) {
          $display_msg = 'Error: No name & invalid email address provided';
        } else {
          $display_msg = 'Error: invalid email address provided';
        }
        $err = true;
      }
      if (array_key_exists('phone', $_POST)) {
        $phone = substr(strip_tags($_POST['phone']), 0, 255);
      } else {
        $phone = 'No phone number provided';
      }
      if (array_key_exists('subject', $_POST)) {
        $subject = substr(strip_tags($_POST['subject']), 0, 255);
      } else {
        $subject = 'No subject provided';
      }
      if (array_key_exists('company', $_POST)) {
        $company = substr(strip_tags($_POST['company']), 0, 255);
      } else {
        $company = 'No company provided';
      }
      if (array_key_exists('budget', $_POST) && in_array($_POST['budget'], ['under-5k', '5-10k', 'over-10k', 'not-applicable'], true)) {
        $budget = $_POST['budget'];
      } else {
        $budget = 'No budget provided';
      }
      
      // if no error exists, setup to do the mailer stuff. only if 'name' & 'email' exists & are validated
      if (!$err) {
        
        // mailer settings
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->isHTML(true);
        $mail->Host = 'localhost';
        $mail->Port = 25;
        $mail->Host = $this->secret['mail_host']; // secret
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->secret['mail_u']; // secret
        $mail->Password   = $this->secret['mail_p']; // secret
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        
        // mailer form settings
        $mail->setFrom('info@robertmccormack.com', (empty($name) ? 'Contact form' : $name));
        $mail->addAddress('cv@robertmccormack.com');
        $mail->addReplyTo($email, $name);
        $mail->Subject = $subject ? 'New Contact form: '.$subject : 'New Contact form submission';
        $mail->Body = $mail->msgHTML($this->twig->render('contact-form-template.twig',array(
          'name' => $name,
          'email' => $email,
          'phone' => $phone,
          'subject' => $subject,
          'company' => $company,
          'budget' => $budget,
        )));
  
        //Send the message, check for errors
        if (!$mail->send()) {
          
          //The reason for failing to send will be in $mail->ErrorInfo
          //but it's unsafe to display errors directly to users - process the error, log it on your server.
          if($isAjax) http_response_code(500);
  
          $response = [
            "status" => false,
            "message" => '<p class="uk-margin uk-text-danger uk-text-bold uk-margin">Sorry, something went wrong. Please try again later.</p>'
          ];
            
        } else {
          $response = [
            "status" => true,
            "message" => '<p class="uk-margin uk-text-success uk-text-bold uk-margin">Thank you, your message has been sent successfully. We will be in contact shortly.</p>'
          ];
        }
        
        $form_array = array(
          'err' => $err,
          'response' => $response,
          'name' => $name,
          'email' => $email,
          'phone' => $phone,
          'subject' => $subject,
          'company' => $company,
          'budget' => $budget,
          'mail' => $mail
        );
        
      } else {
        // if error exists, set the resonse data with the error/display message & set status to false
        $response = [
          "status" => false,
          "message" => '<p class="uk-margin uk-text-warning uk-text-bold uk-margin">'.$display_msg.'</p>'
        ];
        $form_array = array(
          'err' => $err,
          'form_display_msg' => $display_msg,
          'response' => $response,
        );
      }
      
      // Turn the form data into an array to add to the twig context
      foreach($form_array as $key => $value){
        $context[$key] = $value;
      }
  
      // if is an ajax request, return the resonse as json
      if ($isAjax) {
        header('Content-type:application/json;charset=utf-8');
        echo json_encode($response);
        exit();
      }
    }
    $context['single'] = (new SingleModel($this->type, $this->slug))->single;
    $context['context'] = $_context;
    $this->render($context);
  }
  
  public function getSingle() {
    global $_context;
    $context['single'] = (new SingleModel($this->type, $this->slug))->single;
    $context['context'] = $_context;
    
    // if homepage, we will get some posts from json to display on homepage, like a products grid
    // these posts are essentially like a private cpt on wordpress, they can be displayed but are not routed or archived
    // if archived routes were enabled, this cpt would become live.. perhaps non-public cpts can be defined in config, then checked against in archived routes
    // so as to not create routes for that cpt, thereby making it private (non-routed but displayable) 
    if($this->slug == 'index') {
      
      // products (blinds)
      $blinds_args_array = array(
        // working. takes string & content type singular label (post, project etc...)
        'type' => 'product',
        
        // // working. takes array with relation key & sub-arrays
        'tax_query' => array(
          // relation is required for now as there are checks based the iteration of the first array in this sequence, which should be 2nd when relation exists
          'relation' => 'AND', // working: 'AND', 'OR'. Deaults to 'AND'.
          array(
            'taxonomy' => 'category', // working. takes taxonomy string
            'terms' => array('blinds'), // working. takes array
            'operator' => 'AND' // 'AND', 'IN', 'NOT IN'. 'IN' is default. AND means posts must have all terms in the array. In means just one.
          )
        ),
        // 
        // // working. takes string & searches in title/excerpt for matches
        // 's' => 'lorem',
        // 
        // // working. takes array with date arguments.
        // 'date' => array(
        //   'year'  => 2021,
        //   'month' => 4,
        //   'day'   => 4,
        // ),
        // 
        // // working. takes string & searches in slugs for matches
        // 'name' => 'sed',
        // 
        // 'orderby' => 'title', // title, slug: title is default
        // 'order' => 'DESC', // ASC DESC: ASC is default
        // 
        // // the pagination stuff. seems to be working...
        // 'per_page' => 3,
        // 'p' => 1,
        
        'show_all' => true
      );
      $blinds_obj = new QueryModel($blinds_args_array);
      $blinds = $blinds_obj->posts;
      $context['blinds'] = $blinds;
      
      $hero_banners_args_array = array(
        // working. takes string & content type singular label (post, project etc...)
        'type' => 'hero_banner',
        
        'per_page' => 1,
        'p' => 1
        // 'show_all' => true
      );
      $hero_banners_obj = new QueryModel($hero_banners_args_array);
      $hero_banners = $hero_banners_obj->posts;
      $context['hero_banners'] = $hero_banners;
      
      $highlight_blocks_args_array = array(
        // working. takes string & content type singular label (post, project etc...)
        'type' => 'highlight_block',
        'show_all' => true
      );
      $highlight_blocks_obj = new QueryModel($highlight_blocks_args_array);
      $highlight_blocks = $highlight_blocks_obj->posts;
      $context['highlight_blocks'] = $highlight_blocks;
      
      $banner_blocks_args_array = array(
        // working. takes string & content type singular label (post, project etc...)
        'type' => 'banner_block',
        'show_all' => true
      );
      $banner_blocks_obj = new QueryModel($banner_blocks_args_array);
      $banner_blocks = $banner_blocks_obj->posts;
      $context['banner_blocks'] = $banner_blocks;
      
      $cats_args_array = array(
        // the query stuff...
        'type' => 'shop', // REQUIRED
        'taxonomy' => 'category', // REQUIRED
        // the pagination stuff...
        // 'per_page' => 3,
        // 'p' => 1,
        'show_all' => true
      );
      $cats_obj = new QueryTermsModel($cats_args_array);
      $cats = $cats_obj->terms;
      $context['categories'] = $cats;
      
    }
    
    $this->render($context);
  }
  
  /*
  *
  * This method is used to render singular objects according to a template hierarchy.
  *
  */
  protected function render($context) {
    $is_published = ($context['single']['status'] == 'published');
    $is_draft = ($context['single']['status'] == 'draft');
    $is_author_ip = ($_SERVER['REMOTE_ADDR'] == $this->secret['local_ip']);
    
    if ($context['single'] && (($is_draft && $is_author_ip) || $is_published)) {
      
      $_type = (isset($context['single']['type'])) ? $context['single']['type'] : $this->name;
      $_format = (isset($context['single']['format'])) ? $context['single']['format'] : 'default';
      $_slug = $context['single']['slug'];
      
      $slugged = slugToFilename($_slug);
      $format1 = $slugged.'.twig'; // creativo-para-jovenes.twig
      $format2 = $_type.'_'.$_format.'.twig'; // post_video.twig
      $format3 = $_type.'.twig'; // post.twig
      
      if($this->twig->getLoader()->exists($format1)){
        $this->templateRender($format1, $context);
        exit();
      }
      
      if($this->twig->getLoader()->exists($format2)) {
        $this->templateRender($format2, $context);
        exit();
      }

      if($this->twig->getLoader()->exists($format3)) {
        $this->templateRender($format3, $context);
        exit();
      }
      
      else {
        $this->templateRender('single.twig', $context);
      }
      
    } else {
      $this->error();
    }
  }
}