<?php

/**
 * @file
 *
 * Defines a class for NPRML creation/transmission and retreival/parsing
 * Unlike NPRAPI class, NPRAPIDrupal is drupal-specific
 */
require_once ('NPRAPI.php');
require_once ('nprml.php');

class NPRAPIWordpress extends NPRAPI {

  /**
   * Makes HTTP request to NPR API.
   *
   * @param array $params
   *   Key/value pairs to be sent (within the request's query string).
   *
   *
   * @param string $path
   *   The path part of the request URL (i.e., http://example.com/PATH).
   *
   * @param string $base
   *   The base URL of the request (i.e., HTTP://EXAMPLE.COM/path) with no trailing slash.
   */
  function request($params = array(), $path = 'query', $base = self::NPRAPI_PULL_URL) {

    $this->request->params = $params;
    $this->request->path = $path;
    $this->request->base = $base;

    $queries = array();
    foreach ($this->request->params as $k => $v) {
      $queries[] = "$k=$v";
    }
    $request_url = $this->request->base . '/' . $this->request->path . '?' . implode('&', $queries);
    $this->request->request_url = $request_url;
    $this->query_by_url($request_url);
  }
  
  /**
   * 
   * Query a single url.  If there is not an API Key in the query string, append one, but otherwise just do a straight query
   * 
   * @param string $url -- the full url to query.
   */
  function query_by_url($url){
  	//check to see if the API key is included, if not, add the one from the options
  	if (!stristr($url, 'apiKey=')){
  		$url .= '&apiKey='. get_option( 'ds_npr_api_key' );
  	}
  	
  	$this->request->request_url = $url;

    $response = wp_remote_get( $url );
    if( !is_wp_error( $response ) ) {
	    $this->response = $response;
	    if ($response['response']['code'] == self::NPRAPI_STATUS_OK) {

	      if ($response['body']) {
	        $this->xml = $response['body'];
	      }
	      else {
	        $this->notice[] = t('No data available.');
	      }
	    }
	    else {
	    	ds_npr_show_message('An error occurred pulling your story from the NPR API.  The API responded with message ='. $response['response']['message'], TRUE );
	    }
    }
    else {
    	$error_text = '';
    	if (!empty($response->errors['http_request_failed'][0])){
	    	$error_text = '<br> HTTP Error response =  '. $response->errors['http_request_failed'][0];
    	}
    	ds_npr_show_message('Error pulling story for url='.$url . $error_text, TRUE);
    	error_log('Error retrieving story for url='.$url);
    }
  }
  
  /**
   * 
   * This function will go through the list of stories in the object and check to see if there are updates
   * available from the NPR API if the pubDate on the API is after the pubDate originally stored locally.
   * 
   * @param unknown_type $publish
   */
	function update_posts_from_stories($publish = TRUE ) {
		$pull_post_type = get_option('ds_npr_pull_post_type');;
		if (empty($pull_post_type)){
			$pull_post_type = 'post';
		}

		if (!empty($this->stories)){
			$single_story = TRUE;
			if (sizeof($this->stories) > 1){
				$single_story = FALSE;
			}
			foreach ($this->stories as $story) {
	        $exists = new WP_Query( array( 'meta_key' => NPR_STORY_ID_META_KEY, 
	                                       'meta_value' => $story->id,
	        															 'post_type' => $pull_post_type ) );
	        //set the mod_date and pub_date to now so that for a new story we will fail the test below and do the update
	        $post_mod_date = strtotime(date('Y-m-d H:i:s'));
	        $post_pub_date = $post_mod_date;
	        if ( $exists->post_count ) {
	            $existing = $exists->post;
	            $post_id = $existing->ID;	            
	            $existing_status = $exists->posts[0]->post_status;
	            $post_mod_date_meta = get_post_meta($existing->ID, NPR_LAST_MODIFIED_DATE_KEY);
	            if (!empty($post_mod_date_meta[0])){
		            $post_mod_date = strtotime($post_mod_date_meta[0]);
	            }
	            $post_pub_date_meta = get_post_meta($existing->ID, NPR_PUB_DATE_META_KEY);
	            if (!empty($post_pub_date_meta[0])){
	            	$post_pub_date = strtotime($post_pub_date_meta[0]);
	            }
	        }
	        else {
	            $existing = null;
	        }
    
	        //add the transcript

					$story->body .= $this->get_transcript_body($story);

	        //set the story as draft, so we don't try ingesting it
	        $args = array(
	            'post_title'   => $story->title,
	            'post_excerpt' => $story->teaser,
	            'post_content' => $story->body,
	        		'post_status'  => 'draft',
	        		'post_type'    => $pull_post_type,
	        );
					//check the last modified date and pub date (sometimes the API just updates the pub date), if the story hasn't changed, just go on
					if (($post_mod_date != strtotime($story->lastModifiedDate->value))  || ($post_pub_date !=  strtotime($story->pubDate->value)) ){
		        

//var_dump($story->byline->links);  var_dump($story->audio); exit;
		        //set the meta RETRIEVED so when we publish the post, we dont' try ingesting it
		        $metas = array(
		            NPR_STORY_ID_META_KEY      => $story->id,
		            NPR_API_LINK_META_KEY      => $story->link['api']->value,
		            NPR_HTML_LINK_META_KEY     => $story->link['html']->value,
		            //NPR_SHORT_LINK_META_KEY    => $story->link['short']->value,
		            NPR_STORY_CONTENT_META_KEY => $story->body,
		            //NPR_BYLINE_META_KEY        => $by_line,
		            //NPR_BYLINE_LINK_META_KEY   => $byline_link,
		            NPR_RETRIEVED_STORY_META_KEY => 1,
		            NPR_PUB_DATE_META_KEY => $story->pubDate->value,
		            NPR_STORY_DATE_MEATA_KEY => $story->storyDate->value,
								NPR_LAST_MODIFIED_DATE_KEY=> $story->lastModifiedDate->value,
								
		        );
		        
		        //get byline
				//$byline_link = '';
		        if (isset($story->byline)){
		        	$by_line_array = array();
		        	foreach($story->byline as $byline){
			        	
			        	$by_line_array[] = $byline->name->value;
			        	
		        	}
		        	
		        	/*
		        	$by_line = $story->byline->name->value;
		        	if (!empty($story->byline->links)){
		        		foreach($story->byline->links as $link){
		        			if ($link->type == 'html'){
		        				$byline_link = $link->value;
		        			}
		        		}
		        	}*/
		        	
		        $metas[npr_byline] = implode("; ", $by_line_array);	
		        }
		        
		        //get provider org
		        if ( isset($story->organization)){
			        $metas[provider] = $story->organization->name->value;
			        
		        }
		        
		        //get external assets
		        if ( isset($story->externalAsset)){
			        $youtube_array = array();
			        if( is_array($story->externalAsset)){
			        foreach ($story->externalAsset as $asset){
				        if ($asset->type == "YouTube"){
				        
				        if(!in_array($asset->url->value, $youtube_array)){
				        	$youtube_array[] = array(
				        		'id' => $asset->id,
				        		'url' => $asset->url->value,
				        		'oEmbed' => $asset->oEmbed->value,
				        		'credit' => $asset->credit->value,
				        		'parameters' => $asset->parameters->value,
				        		'caption' => $asset->caption->value,
				        		);
						}

				 			       
				        						
					}

			      }
			      		$temp = '';
			      		foreach($youtube_array as $item){
				      		$temp .= implode(" \n ", $item);	
			      		}
			      		 
					  	
					  	$temp .= " \n " . $story->body;
					  	
					  	$story->body = $temp;
			      
			      /*else{
				      if($story->externalAsset->type == 'YouTube'){
					      
					  	$youtube_array[] = $story->externalAsset->url->value;   
					  	
					  	$temp = implode(" \n ", $youtube_array); 
					  	
					  	$temp .= " \n " . $story->body;
					  	
					  	$story->body = $temp;
				      }
				      
				   */   
				      
			      }
				  $metas[npr_external_assets_youtube] = json_encode($youtube_array);
		        }
			    ob_start();
			    var_dump($story);
			    $metas[trouble] = ob_get_clean();   
		        
		        //get multimedia
		        if ( isset($story->multimedia) ) {
		        	$mp4_array = array();
		        	foreach ($story->multimedia as $item){
								if (!empty($item->format->mp4) && $item->permissions->embed->allow == 'true'){
										$mp4_array[] = array(
										'id' => $item->id,
										'title' => $item->title->value,
										'src' => explode('?', $item->format->mp4->value)[0],
										'width' => $item->width->value,
										'height' => $item->height->value,
										'rightsHolder' => $item->rightsHolder->value,
										'caption' => $item->caption->value,
										'credit' => $item->credit->value,
										'altImageUrl' => $item->altImageUrl->value,
										);	

								}
		        	}
		        	$metas[npr_multimedia_mp4] =  json_encode($mp4_array);
					
					$temp = implode(" \n ", $mp4_array[0]); 
					  	
					$temp .= " \n ". $story->body;
					  	
					$story->body = $temp;

		        }

		        //get audio
		        if ( isset($story->audio) ) {
		        	$mp3_array = array();
		        	$m3u_array = array();
		        	foreach ($story->audio as $n => $audio){
								if (!empty($audio->format->mp3['mp3']) && $audio->permissions->download->allow == 'true'){
									if ($audio->format->mp3['mp3']->type == 'mp3' ){
										$mp3_array[] = explode('?', $audio->format->mp3['mp3']->value)[0];	
									}
									if ($audio->format->mp3['m3u']->type == 'm3u' ){
										$m3u_array[] = explode('?', $audio->format->mp3['m3u']->value)[0];
									}
								}
		        	}
		        	$metas[NPR_AUDIO_META_KEY] =  implode(',', $mp3_array);
		        	$metas[NPR_AUDIO_M3U_META_KEY] = implode(',', $m3u_array); 
					
					$temp = implode(" \n ", $mp3_array); 
					  	
					$temp .= " \n ". $story->body;
					  	
					$story->body = $temp;

		        }
				//get products
		        if ( isset($story->product) ) {
		        	$product_array = array();
		        	foreach ($story->product as $item){
						$product_array[] = array(
						'id' => $item->id,
						'author' => $item->author->value,
						'type' => $item->type,
						'upc' => $item->upc->value,
						'title' => $item->title->value,
						'publisher' => $item->publisher->value,
						'publishYear' => $item->publishYear->value,
						'productLink' => array(
							'Amazon' => $item->productLink['Amazon']->value,
							'iTunes' => $item->productLink['iTunes']->value,
							),
						);	
					}
		        	
		        	$metas[related_products] =  json_encode($product_array);
					
				}
		        if ( $existing ) {
		            $created = false;
		            $args[ 'ID' ] = $existing->ID;
		        }
		        else {
		            $created = true;
		        }
		        $post_id = wp_insert_post( $args );

		        //now that we have an id, we can add images
		        //this is the way WP seems to do it, but we couldn't call media_sideload_image or media_ because that returned only the URL
		        //for the attachment, and we want to be able to set the primary image, so we had to use this method to get the attachment ID.
		        		//get images
						if (isset($story->image[0])){
							
							//are there any images saved for this post, probably on update, but no sense looking of the post didn't already exist
							if ($existing){
								$image_args = array(
									'order'=> 'ASC',
									'post_mime_type' => 'image',
									'post_parent' => $post_id,
									'post_status' => null,
									'post_type' => 'attachment'
									);
								$attached_images = get_children( $image_args );
							}
							
							
		        	foreach ($story->image as $image){

		        		// Download file to temp location
		        	$tmp = NULL;
		        	if($image->enlargement->src != NULL){
		            	$tmp = download_url( $image->enlargement->src );
		            }else if($image->crop['standard']->src != NULL){
			            
			            $tmp = download_url( $image->crop['standard']->src );
		            }
		            
		            // Set variables for storage
		            // fix file filename for query strings
		            preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $image->src, $matches);
		            $file_array['name'] = basename($matches[0]);
		            $file_array['tmp_name'] = $tmp;

		            $file_OK = TRUE;
		            // If error storing temporarily, unlink
		            if ( is_wp_error( $tmp ) ) {
		            	@unlink($file_array['tmp_name']);
		              $file_array['tmp_name'] = '';
		              $file_OK = FALSE;
		            }

		            // do the validation and storage stuff
		            $id = media_handle_sideload( $file_array, $post_id, '', array('post_title' => $image->title->value, 'post_content' => '','post_excerpt' => $image->caption->value) );
		            /*
		            $img_meta = array(
		            	'title' => $image->title->value,
		            	'caption' => $image->caption->value,
		            	'link' => $image->link->url,
		            	'producer' => $image->producer->value,
		            	'provider' => $image->provider->value,
		            	'provider_url' => $image->provider->url,
		            	'copyright' => $image->copyright->value,
		            	);
		            */
		            //update_post_meta($id, 'post_excerpt', $image->caption->value);
		            update_post_meta($id, '_link', $image->link->url);
		            update_post_meta($id, '_producer', $image->producer->value);
		            update_post_meta($id, '_provider', $image->provider->value);
		            update_post_meta($id, '_provider_url', $image->provider->url);
		            update_post_meta($id, '_copyright', $image->copyright->value);
		            
		            
		            // If error storing permanently, unlink
		            if ( is_wp_error($id) ) {
		            	@unlink($file_array['tmp_name']);
		            	$file_OK - FALSE;
		            }
		            else {
		            	$image_post = get_post($id);
		            	if (!empty($attached_images)) {
			            	foreach($attached_images as $att_image){
			            			//see if the filename is very similar
			            			$att_guid = explode('.', $att_image->guid);
			            			//so if the already attached image name is part of the name of the file
			            			//coming in, ignore the new/temp file, it's probably the same
			            			if (strstr($image_post->guid, $att_guid[0])){
			            				@unlink($file_array['tmp_name']);
			            				wp_delete_attachment($id);
			            				$file_OK - FALSE;
			            			}
			            	}
		            	}
		            }

		            //set the primary image
		            if ($image->type == 'primary' && $file_OK){
		            	add_post_meta($post_id, '_thumbnail_id', $id, true);
		            }

		        	}
		        }//end get images
		        
		        		//get static graphics
						if (isset($story->staticGraphic[0])){
							
							//are there any images saved for this post, probably on update, but no sense looking of the post didn't already exist
							if ($existing){
								$image_args = array(
									'order'=> 'ASC',
									'post_mime_type' => 'image',
									'post_parent' => $post_id,
									'post_status' => null,
									'post_type' => 'attachment'
									);
								$attached_images = get_children( $image_args );
							}
							
							
		        	foreach ($story->staticGraphic as $image){

		        		// Download file to temp location
		        	$tmp = NULL;
		        	if($image->src != NULL){
		            	$tmp = download_url( $image->src );
		            }
		            
		            // Set variables for storage
		            // fix file filename for query strings
		            preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $image->src, $matches);
		            $file_array['name'] = basename($matches[0]);
		            $file_array['tmp_name'] = $tmp;

		            $file_OK = TRUE;
		            // If error storing temporarily, unlink
		            if ( is_wp_error( $tmp ) ) {
		            	@unlink($file_array['tmp_name']);
		              $file_array['tmp_name'] = '';
		              $file_OK = FALSE;
		            }

		            // do the validation and storage stuff
		            $id = media_handle_sideload( $file_array, $post_id, '', array('post_title' => $image->altText->value, 'post_content' => '','post_excerpt' => $image->description->value) );
		            /*
		            $img_meta = array(
		            	'title' => $image->title->value,
		            	'caption' => $image->caption->value,
		            	'link' => $image->link->url,
		            	'producer' => $image->producer->value,
		            	'provider' => $image->provider->value,
		            	'provider_url' => $image->provider->url,
		            	'copyright' => $image->copyright->value,
		            	);
		            */
		            //update_post_meta($id, 'post_excerpt', $image->caption->value);
		            //update_post_meta($id, '_link', $image->link->url);
		            update_post_meta($id, '_producer', $image->credit->value);
		            //update_post_meta($id, '_provider', $image->provider->value);
		            //update_post_meta($id, '_provider_url', $image->provider->url);
		            //update_post_meta($id, '_copyright', $image->copyright->value);
		            
		            // If error storing permanently, unlink
		            if ( is_wp_error($id) ) {
		            	@unlink($file_array['tmp_name']);
		            	$file_OK - FALSE;
		            }
		            else {
		            	$image_post = get_post($id);
		            	if (!empty($attached_images)) {
			            	foreach($attached_images as $att_image){
			            			//see if the filename is very similar
			            			$att_guid = explode('.', $att_image->guid);
			            			//so if the already attached image name is part of the name of the file
			            			//coming in, ignore the new/temp file, it's probably the same
			            			if (strstr($image_post->guid, $att_guid[0])){
			            				@unlink($file_array['tmp_name']);
			            				wp_delete_attachment($id);
			            				$file_OK - FALSE;
			            			}
			            	}
		            	}
		            }

		            //set the primary image
		            if ($image->type == 'primary' && $file_OK){
		            	add_post_meta($post_id, '_thumbnail_id', $id, true);
		            }

		        	}
		        }//end static graphics

		        foreach ( $metas as $k => $v ) {
		            update_post_meta( $post_id, $k, $v );
		        }

		        $args = array(
		        		'post_title'   => $story->title,
		            'post_content' => $story->body,
		        		'post_excerpt' => $story->teaser,
		        		'post_type'    => $pull_post_type,  
		            'ID'   => $post_id,
		        );
					 //now set the status
						if ( ! $existing ) {
		        	if ($publish){
		            $args['post_status'] = 'publish';
		        	}
		        	else {
		        		$args['post_status'] = 'draft';
		        	}
		        }
		        else {
		        	//if the post existed, save its status
		        	$args['post_status'] = $existing_status;
		        }
		        
		        
		        //$args['post_date'] = date_format(date_create($story->storyDate, timezone_open('America/New_York')),'Y-m-d H:i:s') ;
		        $ret = wp_insert_post( $args );
		        //set categories
		        if(isset($story->tax)){
		        	$category = array();
		        	$genre = array();
		        	$series = array();
			        foreach($story->tax as $tax){
				        if(($tax->type == 'category' || $tax->type == 'topic') && ($tax->title->value != 'Home Page Top Stories' && $tax->title->value != 'Music')){
					        //wp_set_object_terms( $post_id, $tax->title->value, 'category' );
							$category[] = $tax->title->value;
				        }
				        if($tax->type == 'genre'){
					        //wp_set_object_terms( $post_id, $tax->title->value, 'genre' );
					        $genre[] = $tax->title->value;
				        }
				        if($tax->type == 'series'){
					        //wp_set_object_terms( $post_id, $tax->title->value, 'series' );
					        $series[] = $tax->title->value;
				        }
			        }
					if(isset($category[0])){
						wp_set_object_terms( $post_id, $category, 'category' );
					}
					if(isset($genre[0])){
						wp_set_object_terms( $post_id, $genre, 'genre' );
					}
					if(isset($series[0])){
						wp_set_object_terms( $post_id, $series, 'series' );
					}
		        
		        }
		        
					}
			}
			if ($single_story){
				return $post_id;
			}
		}

    return;
	}



  /**
   * Create NPRML from wordpress post.
   *
   * @param object $post
   *   A wordpress post.
   *
   * @return string
   *   An NPRML string.
   */
  function create_NPRML($post) {
		//using some old helper code
		return as_nprml($post);
  }

  /**
   * This function will send the push request to the NPR API to add/update a story.
   * 
   * @see NPRAPI::send_request()
   */
  function send_request ($nprml, $post_ID) {
		$error_text = '';
  	$org_id = get_option( 'ds_npr_api_org_id' );
  	if (!empty($org_id)){
	    $url = add_query_arg( array( 
	        'orgId'  => $org_id,
	        'apiKey' => get_option( 'ds_npr_api_key' )
	    ), get_option( 'ds_npr_api_push_url' ) . '/story' );

	    //error_log('Sending nprml = '.$nprml);
	    
	    $result = wp_remote_post( $url, array( 'body' => $nprml ) );
	    if ( !is_wp_error($result) ) {
		    if(  $result['response']['code'] == 200 ) {
			    $body = wp_remote_retrieve_body( $result );
			    if ( $body ) {
			        $response_xml = simplexml_load_string( $body );
			        $npr_story_id = (string) $response_xml->list->story['id'];
			        update_post_meta( $post_ID, NPR_STORY_ID_META_KEY, $npr_story_id );
			    }
			    else {
			        error_log( 'NPR API Push ERROR: ' . print_r( $result, true ) );
			    }
		    }
		    else {
		    	$error_text = '';
		    	if (!empty($result['response']['message'])){
			    	$error_text = 'Error pushing story with post_id = '. $post_ID .' for url='.$url . ' HTTP Error response =  '. $result['response']['message'];
		    	}
		    	$body = wp_remote_retrieve_body( $result );
		    	
			    if ( $body ) {
			    	$response_xml = simplexml_load_string( $body );
			    	$error_text .= '  API Error Message = ' .$response_xml->message->text;
			    }
		    	error_log('Error returned from API ' . $error_text);
		    }
	    } else {
	    	error_log('WP Error returned from sending story with post_id = '. $post_ID .' for url='.$url . ' to API ='. $result->get_error_message());
	    }
  	} else {
  		$error_text = 'Tried to push, but OrgID was not set for post_id ='. $post_ID;
  		error_log($error_text);
  	}

		if (!empty($error_text)){
	  	update_post_meta( $post_ID, NPR_PUSH_STORY_ERROR, $error_text );
		}
		else {
			delete_post_meta($post_ID, NPR_PUSH_STORY_ERROR);
		}
  
  }

  /**
   * 
   * Because wordpress doesn't offer a method=DELETE for wp_remote_post, we needed to write a curl version to send delete 
   * requests to the NPR API
   * 
   * @param  $api_id
   */
  function send_delete($api_id){
  	
  	$url = add_query_arg( array( 
        'orgId'  => get_option( 'ds_npr_api_org_id' ),
        'apiKey' => get_option( 'ds_npr_api_key' ),
  			'id' => $api_id
    ), get_option( 'ds_npr_api_push_url' ) . '/story' );
		//wp doesn't let me do a wp_remote_post with method=DELETE so we have to make our own curl request.  fun
		//a lot of this code came from WP's class-http object
		//$result = wp_remote_post( $url, array( 'method' => 'DELETE' ) );
		$handle = curl_init();
		curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, 'DELETE' );
	  curl_setopt( $handle, CURLOPT_URL, $url);
	  curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
		curl_exec( $handle );
		curl_close( $handle );

  }

  /**
   * 
   * This function will check a story to see if there are transcripts that should go with it, if there are
   * we'll return the transcript as one big strang with Transcript at the top and each paragraph separated by <p>
   * 
   * @param  $story
   */
  function get_transcript_body($story){
  	$transcript_body = "";
	  if (!empty($story->transcript)){
	  	foreach ($story->transcript as $transcript){
	    	if ($transcript->type == 'api'){
			  	$response = wp_remote_get( $transcript->value );
    			if( !is_wp_error( $response ) ) {
    				$transcript_body .= "<p><strong>Transcript :</strong><p>";
    				$body_xml = simplexml_load_string($response['body']);
    				if (!empty($body_xml->paragraph)){
	    				foreach($body_xml->paragraph as $paragraph){
	    					$transcript_body .= (strip_tags($paragraph)) . '<p>';
	    				}
    				}
    			}
	    	}
	  	}
	  }
	  return $transcript_body;
  }
}
