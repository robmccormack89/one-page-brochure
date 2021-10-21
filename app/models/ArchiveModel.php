<?php
namespace Rmcc;

class ArchiveModel {
  
  public function getQueriedArchive() {
    
    global $_context;
    
    /*
    *
    * 1. Use the QueryModel to get the posts object using the string_params
    *
    */
    $posts_obj = new QueryModel($_context['string_params']);
    
    /*
    *
    * 2. Set the archive data; the meta data for the archive
    * We can get the data from the $posts_obj->queried_object
    * Also, if the requested paged page is greater than 1, we modify the archive title to reflect the paged page
    *
    */
    $archive = $posts_obj->queried_object;
    
    /*
    *
    * 3. Set the archive posts data
    * We can get the data from the $posts_obj->posts
    *
    */
    $archive['posts'] = $posts_obj->posts;
    
    /*
    *
    * 4. Set the archive pagination data
    * We use a new PaginationModel->getPagination() object to set the pagination data
    *
    * This may be incorporated into QueryModel as a returned property like $posts_obj->pagination
    *
    */
    if(!empty($archive['posts'])){
      $archive['pagination'] = (new PaginationModel($posts_obj->found_posts))->getPagination();
      if($_context['page'] > 1) $archive['title'] = $archive['title'].' (Page '.$_context['page'].')';
    }
    
    return $archive;
  }

  public function getArchive() {
    
    global $_context;

    /*
    *
    * 1. Use the QueryModel to get the posts object using the $args
    * The $args are made up from ArchiveModel properties
    *
    */
    $args = array(
      'type' => typeSettingByKey('key', $_context['type'], 'single'),
      'per_page' => $_context['per_page'],
      'p' => $_context['page'],
      'show_all' => ($_context['paged']) ? false : true
    );
    $posts_obj = new QueryModel($args);
    
    /*
    *
    * 2. Set the archive data; the meta data for the archive
    * We can get the data from the $posts_obj->queried_object
    * Also, if the requested paged page is greater than 1, we modify the archive title to reflect the paged page
    *
    */
    $archive = $posts_obj->queried_object;
    
    /*
    *
    * 3. Set the archive posts data
    * We can get the data from the $posts_obj->posts
    *
    */
    $archive['posts'] = $posts_obj->posts;
    
    /*
    *
    * 4. Set the archive pagination data
    * We use a new PaginationModel->getPagination() object to set the pagination data
    *
    * This may be incorporated into QueryModel as a returned property like $posts_obj->pagination
    *
    */
    if(!empty($archive['posts'])){
      $archive['pagination'] = (new PaginationModel($posts_obj->found_posts))->getPagination();
      if($_context['page'] > 1) $archive['title'] = $archive['title'].' (Page '.$_context['page'].')';
    }

    return $archive;
  }
  
  public function getTermArchive() {
    
    global $_context;
    
    /*
    *
    * 1. Use the QueryModel to get the posts object using the $args
    * The $args are made up from TermArchiveModel properties
    *
    */
    $args = array(
      'type' => typeSettingByKey('key', $_context['type'], 'single'),
      'tax_query' => array(
        // relation is required for now as there are checks based the iteration of the first array in this sequence, which should be 2nd when relation exists
        'relation' => 'AND', // working: 'AND', 'OR'. Deaults to 'AND'.
        array(
          'taxonomy' => taxSettingByKey($_context['type'], 'key', $_context['tax'], 'single'), // working. takes taxonomy string taxSettingByKey($type_archive, 'single', $value['taxonomy'], 'key');
          'terms' => array($_context['term']), // working. takes array
          'operator' => 'AND' // 'AND', 'IN', 'NOT IN'. 'IN' is default. AND means posts must have all terms in the array. In means just one.
        ),
      ),
      'per_page' => $_context['per_page'],
      'p' => $_context['page'],
      'show_all' => ($_context['paged']) ? false : true
    );
    $posts_obj = new QueryModel($args);
    
    /*
    *
    * 2. Set the archive data; the meta data for the archive
    * We get the data from QueriedObjectModel->getQueriedObject()
    * Also, if the requested paged page is greater than 1, we modify the archive title to reflect the paged page
    *
    * We may want to get the data using $posts_obj->queried_object instead. QueryModel needs to be modified to do this
    *
    */
    $archive = $posts_obj->queried_object;
    
    /*
    *
    * 3. Set the archive posts data
    * We can get the data from the $posts_obj->posts
    *
    */
    $archive['posts'] = $posts_obj->posts;
    
    /*
    *
    * 4. Set the archive pagination data
    * We use a new PaginationModel->getPagination() object to set the pagination data
    *
    * This may be incorporated into QueryModel as a returned property like $posts_obj->pagination
    *
    */
    if(!empty($archive['posts'])){
      $archive['pagination'] = (new PaginationModel($posts_obj->found_posts))->getPagination();
      if($_context['page'] > 1) $archive['title'] = $archive['title'].' (Page '.$_context['page'].')';
    }
    
    return $archive;
  }
  
  public function getTaxonomyArchive() {
    
    global $_context;
    
    /*
    *
    * 1. Use the QueryTermsModel to get the terms_obj using the $args
    *
    */
    $args = array(
      'taxonomy' => taxSettingByKey($_context['type'], 'key', $_context['tax'], 'single'),
      'per_page' => $_context['per_page'],
      'p' => $_context['page'],
      'show_all' => ($_context['paged']) ? false : true
    );
    $terms_obj = new QueryTermsModel($args);
    
    /*
    *
    * 2. Set the archive data; the meta data for the archive
    *
    */
    $archive = $terms_obj->queried_object;
    
    /*
    *
    * 3. Set the archive posts data
    * We can get the data from the $posts_obj->posts
    *
    */
    $archive['posts'] = $terms_obj->terms;
    
    /*
    *
    * 4. Set the archive pagination data
    * We use a new PaginationModel->getPagination() object to set the pagination data
    *
    * This may be incorporated into QueryModel as a returned property like $posts_obj->pagination
    *
    */
    
    if($_context['paged'] && !empty($archive['posts'])){
      $archive['pagination'] = (new PaginationModel($terms_obj->found_terms))->getPagination();
      if($_context['page'] > 1) $archive['title'] = $archive['title'].' (Page '.$_context['page'].')';
    }
    
    return $archive;
  }
  
  public function getQueriedTaxonomyArchive() {
    
    global $_context;
    
    /*
    *
    * 1. Use the QueryModel to get the posts object using the string_params
    *
    */
    $terms_obj = new QueryTermsModel($_context['string_params']);
    
    /*
    *
    * 2. Set the archive data; the meta data for the archive
    * We can get the data from the $posts_obj->queried_object
    * Also, if the requested paged page is greater than 1, we modify the archive title to reflect the paged page
    *
    */
    $archive = $terms_obj->queried_object;
    
    /*
    *
    * 3. Set the archive posts data
    * We can get the data from the $posts_obj->posts
    *
    */
    $archive['posts'] = $terms_obj->terms;
    
    /*
    *
    * 4. Set the archive pagination data
    * We use a new PaginationModel->getPagination() object to set the pagination data
    *
    * This may be incorporated into QueryModel as a returned property like $posts_obj->pagination
    *
    */
    
    if($_context['paged'] && !empty($archive['posts'])){
      $archive['pagination'] = (new PaginationModel($terms_obj->found_terms))->getPagination();
      if($_context['page'] > 1) $archive['title'] = $archive['title'].' (Page '.$_context['page'].')';
    }
    
    return $archive;
  }
  
}