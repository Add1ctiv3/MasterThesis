<?php

return '

<div id="view-record-panel" class="hidden">

    <div id="view-telecommunication-panel" class="hidden">

        <img src="templates/images/telecommunication.png" width="32" height="32" id="telecommunication-icon" />

        <table id="telecommunication-record-table">
            <tr>
                <td>Caller:</td>
                <td class="record-field" field="caller"></td>
            </tr>
            <tr>
                <td>Called:</td>
                <td class="record-field" field="called"></td>
            </tr>
            <tr>
                <td>Timestamp:</td>
                <td class="record-field" field="timestamp"></td>
            </tr>
            <tr>
                <td>Duration (seconds):</td>
                <td class="record-field" field="duration"></td>
            </tr>
            <tr>
                <td>Weight:</td>
                <td class="record-field" field="weight"></td>
            </tr>
            <tr>
                <td>Insert Timestamp:</td>
                <td class="record-field" field="insert_timestamp"></td>
            </tr>
            <tr>
                <td>Type:</td>
                <td class="record-field" field="type"></td>
            </tr>
        </table>

    </div>

    <div id="view-telephone-panel" class="hidden">

        <img src="templates/images/telephone.png" width="32" height="32" id="telephone-icon" />

        <table id="telephone-record-table">
            <tr>
                <td>Number:</td>
                <td class="record-field" field="number"></td>
            </tr>
            <tr>
                <td>Country Code:</td>
                <td class="record-field" field="country_code"></td>
            </tr>
            <tr>
                <td>Type:</td>
                <td class="record-field" field="type"></td>
            </tr>
            <tr>
                <td>Weight:</td>
                <td class="record-field" type="number" min="0.1" max="1" step="0.1" field="num_weight"></td>
            </tr>
            <tr>
                <td>Insert Timestamp:</td>
                <td class="record-field" field="insert_timestamp"></td>
            </tr>
        </table>
        
        <div id="telephone-record-associations">
            
        </div>
        
    </div>

</div>';

?>