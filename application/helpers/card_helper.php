<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Card helper
 */

function info_card($columnWidth,
                   $bodyTextColor,
                   $cardBgColor,
                   $cardIcon,
                   $bodyText,
                   $footerTextColor,
                   $footerText)
{
    $card_html = '
        <div class="'.$columnWidth.'">
            <div class="card o-hidden h-100" style="background-color:'.$cardBgColor.';color:'.$bodyTextColor.' !important">
                <div class="card-body">
                    <div class="card-body-icon">
                    <i class="fas fa-fw '.$cardIcon.'"></i>
                    </div>
                    <div class="mr-5 h3">'.$bodyText.'</div>
                </div>
                <div class="card-footer clearfix large z-1" style="color:'.$footerTextColor.' !important">
                    <span class="float-left">'.$footerText.'</span>
                </div>
            </div>
        </div>';
		
		return $card_html;
}
?>
