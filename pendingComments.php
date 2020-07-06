<?php
/*
 * Plugin Name: Pending Comments
 * Version: 0.2
 * Description: Плагин создан для создания отложенных комметариев
 * Author: bigpe
 * Author URI: https://t.me/bigpebro
 */


add_action('add_meta_boxes', 'pendingComments_add_custom_box');
function pendingComments_add_custom_box(){
    //Works With Post Pages
    $pages = array('post');
    add_meta_box('pendingComments_id', 'Отложенные комментарии',
        'pendingComments_meta_box_callback', $pages, 'side', 'high');
}

function pendingComments_meta_box_callback($post, $meta){
    //Render Template
    require_once (__DIR__ . "/template.php");
}

add_action('save_post', 'pendingComments_save_postdata');
function pendingComments_save_postdata($post_id) {
    //Check PostData
    if (!isset( $_POST['pendingCommentsComplete']))
        return;
    //Skip Auto Save Actions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    //Check User Rights
    if(!current_user_can('edit_post', $post_id))
        return;
    $metaData = json_decode(stripslashes($_POST['pendingCommentsComplete']), true);
    foreach($metaData as $md) {
        $time = strtotime("{$md['Date']} {$md['Time']}");
        //Add single schedule for every comment
        wp_schedule_single_event($time, 'pendingComments_hook', array($post_id, $md['Author'], $md['Text'], $md['CommentID']));
    }
    update_post_meta($post_id, 'pendingCommentsComments', $metaData);
}

add_action('pendingComments_hook', 'pendingComments_addNewComments', 10, 5);
function pendingComments_addNewComments($post_id, $author, $text, $commentID, $parentComment = 0){
    $data = [
        'comment_post_ID'      => $post_id,
        'comment_author'       => $author,
        'comment_content'      => $text,
        'comment_parent'       => $parentComment,
        'comment_approved'     => 1,
    ];
    $newCommentID = wp_insert_comment(wp_slash($data));
    $metaData = get_post_meta($post_id, 'pendingCommentsComments', true);
    //Successful Posted && Only For Parent Comments
    if($commentID && !$parentComment) {
        $answers = $metaData[$commentID-1]['Answers'];
        foreach($answers as $answer){
            $time = strtotime("{$answer['Date']} {$answer['Time']}");
            //Add single schedule for every answer
            wp_schedule_single_event($time, 'pendingComments_hook', array($post_id, $answer['Author'],
                $answer['Text'], $answer['CommentID'], $newCommentID));
        }
    }
}