<?php
namespace denisog\gah\helpers;

class UserList {
    
    public static function getUserList($adVersion, \AdWordsUser $user) {
        $list = [];
        
        // Get the service, which loads the required classes.
        $userListService = $user->GetService('AdwordsUserListService', $adVersion);
        // Create selector.
        $selector = new \Selector();
        $selector->fields = array('Id', 'Name');

        $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);
        do {
            // Make the get request.
            $page = $userListService->get($selector);

            if (isset($page->entries)) {
                foreach ($page->entries as $userList) {
                    $list[] = ['id' => $userList->id, 'name' => $userList->name];
                }
            } 
            
            // Advance the paging index.
            $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
        } while ($page->totalNumEntries > $selector->paging->startIndex);
        
        return $list;
    }
} 