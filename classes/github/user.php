<?php
/**
 * A Github User account
 * 
 * @property string $login The username
 * @property integer $id An internal user ID
 * @property string $avatar_url The url for a user's avatar
 * @property string $url The API url for this record
 * @property string $name The user's full name
 * @property string $company The user's company
 * @property string $blog A URL for the user's blog, if any
 * @property string $location The user's approximate geographic location
 * @property string $email The user's email address
 * @property boolean $hireable Whether the user can be hired
 * @property string $bio A biography (in Github Flavored Markdown)
 * @property integer $public_repos The user's public repositories
 * @property integer $public_gists The user's public gists
 * @property integer $followers The number of people following this user
 * @property integer $following The number of people the user is following
 * @property string $html_url The user's Github homepage
 * @property Github_Timestamp $created_at When the record was created
 * @property string The type of the user
 */
class Github_User extends Github_Object
{      
   protected $_default_field = 'login';
   protected $_fields = array(
      'login'=> null,
      'id'=> null,
      'avatar_url'=> null,
      'url'=> null,
      'name'=> null,
      'company'=> null,
      'blog'=> null,
      'location'=> null,
      'email'=> null,
      'hireable'=> null,
      'bio'=> null,
      'public_repos'=> null,
      'public_gists'=> null,
      'followers'=> null,
      'following'=> null,
      'html_url'=> null,
      'created_at'=> 'Github_Timestamp',
      'type'=> null
      );
   
}