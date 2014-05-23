<?php

class BlogSettingsController
{

    public function blogs_list()
    {

        $html = '<div class="list">
		<div class="title">' . __('Categories management') . '</div>
		<div class="add-cat-butt" onClick="openPopup(\'addCat\');"><div class="add"></div>' . __('Add section') . '</div>
		<div class="level1">
			<div class="head">
				<div class="title">' . __('Category') . '</div>
				<div class="buttons">
				</div>
				<div class="clear"></div>
			</div>
			<div class="items">';


        $html .= '<div class="level2"><div class="number"></div><div class="title"></div><div class="buttons"></div><div class="posts"></div></div>';


        $html .= '</div></div></div>';
        return $html;
    }
}