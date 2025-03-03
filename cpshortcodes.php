<?php

/*    ######   ########
 * #########   #########
 * ####        ####   ####
 * ####        #########   
 * ####        #######
 * ####        ####            
 * #########   ####
 *    ######   ####
 * 
 * This file creates shortcodes for the support plugin
 * These include:
 *      Showing Various Tickets (by the different types or products, ordered by status (descending)
 *      Form for creating a new ticket (have optional link in the above shortcode for this)
 *      
 */
function cpt_tickets_grid_sc($atts)
{
    ob_start();
    $result = '';
    // Get attributes from the parameters...
    $atts_arr = array(
        'cp_type' => '',
        'cp_product' => '',
        'ul_css_class' => '',
        'li_css_class' => '',
        'new_ticket_class' => '',
        'new_ticket_page' => '',
    );    
    extract(shortcode_atts($atts_arr, $atts));
    
    // Build the array...
    $query_arr = array(
        'post_type' => 'cp_ticket', 
        'cp_support_product' => $cp_product,
        'cp_ticket_types' => $cp_type
    );
    $query = new WP_Query($query_arr);
    
    if($new_ticket_page !== '' )
    {
        echo '<a href="' . $new_ticket_page . '" class="'. $new_ticket_class . '">Create a new ticket</a>';
    }
    echo '<ul class="' . $ul_css_class . '">';
    if($query-> have_posts())
    {
        while($query-> have_posts())
        {
            $query->the_post();
            echo '<li class="' . $li_css_class . '">';
            $title_before = '<p><a href="' . get_permalink() . '">';
            $title_after = '</a></p>';
            echo the_title($title_before, $title_after);
            echo '</li>';
        }
    }
    else
    {
        echo '<li class="' . $li_css_class . '">';
        echo '<p>There are no tickets to show</p>';
        echo '</li>';
    }
    return $result;
}
add_shortcode('cp_tickets_grid', 'cpt_tickets_grid_sc');


function cpt_ticket_add_sc()
{
    $result = '';
    ob_start();
    global $current_user;
    get_currentuserinfo();
    // If they are not logged in then we just exit out of it...
    if ( ! $current_user->exists() )
    {
        echo 'Sorry but you have to be logged in to use this feature...';
        return $result;
    }
    // Get the lists of taxonomies...
    $types = get_terms('cp_ticket_types', array('hide_empty' => false));
    $products = get_terms('cp_support_product', array('hide_empty' => false));
    $statuses = get_terms('cp_ticket_status', array('hide_empty' => false));
    $page_url = ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ? "https://" : "http://" ).$_SERVER["SERVER_NAME"].strip_tags( $_SERVER["REQUEST_URI"] );
    echo
    '<form method="POST" action="' . $page_url . '">
       <table> 
        <tr>
            <td>
            <label for="edTitle">Title</label>
            </td>
            <td>
            <input type="text" name="edTitle"/>
            </td>
        </tr>
        <tr>
            <td>
            <label for="edDetails">Details</label>
            </td>
            <td>
            <textarea rows="4" cols="50" name="edDetails"></textarea>
	</td>
        </tr>
        <tr>
            <td>
            <label for="edProduct">Product</label>
            </td>
            <td>
            <select name="edProduct">';
            
    foreach($products as $prod)
    {
        echo '<option value="' . $prod->term_id . '">' . $prod->name . '</option>';
    }

    echo   '</select>
	</td>
        </tr>
        <tr>
            <td>
            <label for="edStatus">Status</label>
             </td>
            <td>
            <select name="edStatus">';
    foreach($statuses as $stat)
    {
        echo '<option value="' . $stat->term_id . '">' . $stat->name . '</option>';
    }
    
    echo '</select>
	</td>
        </tr>
        <tr>
            <td>
            <label for="edType">Type</label>
             </td>
            <td>
            <select name="edType">';
    foreach($types as $typ)
    {
        echo '<option value="' . $typ->term_id . '">' . $typ->name . '</option>';
    }
    echo '  </select>
	</td>
        </tr></table>
        <input type="submit" value="Create Ticket"/>        
        </form>';
    if(isset($_POST['edTitle']))
    {                   
        $this_user_id = $current_user->ID;
        $tax_arr = array(
            'cp_ticket_status' => array( $_POST['edStatus'] ),
            'cp_ticket_types' => array( $_POST['edType'] ),
            'cp_support_product' => array( $_POST['edProduct'] )
        );
        
        // If we get here then the user is logged in and we have their id...
        $new_post_arr = array(
            'comment_status' => 'open',
            'ping_status' => 'closed',
            'post_author' => $this_user_id,
            'post_content' => $_POST['edDetails'],
            'post_name' => sanitize_title($_POST['edTitle']),
            'post_status' => 'publish',
            'post_title' => $_POST['edTitle'],
            'post_type' => 'cp_ticket',
            'tax_input' => $tax_arr
         );
         wp_insert_post($new_post_arr);
         echo 'Thanks for sending us this request. We will endevour to reply to your ticket as soon as possible.';
    }
    return $result;
}
add_shortcode('cp_ticket_add', 'cpt_ticket_add_sc');
?>
