<?php
/**
 * Holds the details of either the pull or base branch related to a pull request
 * 
 * @property string $label A branch label for the branch
 * @property string $ref The git commit reference
 * @property string $sha The SHA of the commit
 * @property Github_User $user The user who owns this repo
 * @property Github_Repo $repo The repository the commit is contained within
 */
class Github_Repo_Pull_Branch extends Github_Object
{      
   protected $_fields = array(
       'label' => NULL,
       'ref' => NULL,
       'sha' => NULL,
       'user' => 'Github_User',
       'repo' => 'Github_Repo',
       );
}