<?php

return '<div id="create-set-on-import-dialog" class="hidden">
    
            <div class="sets-block">
                <input type="text" id="create-new-set-input" placeholder="New set name..." />
                <select id="new-set-type">
                    <option>Public</option>
                    <option>Private</option>
                </select>
                <input type="button" id="create-new-set-button" value="Create" />
            </div>
    
            <span class="sets-label"><b>Or select one of the existing sets to append the data</b></span>
            <div class="sets-block" id="sets-container">
    
            </div>
    
        </div>';

?>