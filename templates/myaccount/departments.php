<?php
?>
<h3 class="">Practice Addresses</h3>
<button class="create-department alignright"> Add practice</button>
<br>
<?php woocommerce_output_all_notices();?>
<div class="add-department" style="display:none">
  <form method="post" id="department-form">

  <h3>Add New</h3>

  <div class="woocommerce-address-fields">

    <div class="woocommerce-address-fields__field-wrapper">
      <?php
        woocommerce_form_field( 'department_name', array(
          'label'=>'Name',
          'class'=>array('form-row-wide','address-field'),
          'required' =>true,
          'custom_attributes' => array('required' =>true),
          'autocomplete' => 'given-name'), '' );
        woocommerce_form_field( 'country', array(
          'label'=>'Country',
          'class'=>array('form-row-wide','address-field','update_totals_on_change'),
          'required' =>true,
          'custom_attributes' => array('required' =>true),
          'type'=>'country',
          'autocomplete' => 'given-name'), 'US' );
        woocommerce_form_field( 'address', array(
          'label'=>'Address',
          'class'=>array('form-row-wide','address-field'),
          'required' =>true,
          'custom_attributes' => array('required' =>true),
          'autocomplete' => 'address-line1'), '' );
        woocommerce_form_field( 'city', array(
          'label'=>'City',
          'class'=>array('form-row-wide','address-field'),
          'required' =>true,
          'custom_attributes' => array('required' =>true),
          'autocomplete' =>'address-level2'), '' );
        woocommerce_form_field( 'zip_code', array(
          'label'=>'Zip',
          'class'=>array('form-row-wide','address-field'),
          'required' =>true,
          'custom_attributes' => array('required' =>true),
          'validate' => array('postcode'),
          'autocomplete' => 'postal-code'), '' );
        woocommerce_form_field( 'state', array(
          'label'=>'State',
          'type'=>'state',
          'class'=>array('form-row-wide','address-field'),
          'required' =>true,
          'custom_attributes' => array('required' =>true),
          'validate' => array('state'),
          'country_field' => 'country', 
        ), '' );
        woocommerce_form_field( 'note', array(
          'label'=>'Note',
          'type'=>'textarea',
          'class'=>array('form-row-wide','address-field'),
          'country_field' => 'country', 
        ), '' );

  //    }
      ?>
    </div>

    <p>
      <button type="submit" class="button" name="save_address" value="<?php esc_attr_e( 'Save practice address', 'departments' ); ?>"><?php esc_html_e( 'Save address', 'departments' ); ?></button>
      <button type="button" class="cancel-add-button button" name="cancel" value="<?php esc_attr_e( 'Cancel', 'departments' ); ?>"><?php esc_html_e( 'Cancel', 'departments' ); ?></button>
      <?php wp_nonce_field( 'departments-edit_address', 'departments-edit-address-nonce' ); ?>
      <input type="hidden" name="department-action" id="add_department_action" value="edit_address" />
      <input type="hidden" name="department-id" id="add_department_id" value="0" />
    </p>
  </div>

  </form>

</div>
<table>
  <thead>
    <tr>
      <th>Practice</th>
      <th>Address</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php if(!empty($departments)){?>
      <?php foreach($departments as $department){?>
        <tr id="department<?= $department->address_id ?>" class="department-row">
          <td><?= $department->department_name?></td>
          <td>
            <address>
              <div><?= $department->address?></div>
              <div><?= $department->city?></div>
              <div><?= $department->zip_code?></div>
              <div><?= $department->state?></div>
            </address>
          </td>
          <td>
            <button class="edit-department button" data-id="<?= $department->address_id ?>">Edit</button>
            <button class="delete-department button" data-id="<?= $department->address_id ?>">Delete</button>
          </td>
        </tr>
        <tr id="form<?= $department->address_id ?>" style="display:none" class="edit-department-department">
          <td colspan=3>
          <form method="post">

            <h3>Edit </h3>

            <div class="woocommerce-address-fields">

              <div class="woocommerce-address-fields__field-wrapper">
                <?php
                  woocommerce_form_field( 'department_name', array(
                    'label'=>'Name',
                    'class'=>array('form-row-wide','address-field'),
                    'required' =>true,
                    'custom_attributes' => array('required' =>true),
                    'autocomplete' => 'given-name'), $department->department_name );
                  woocommerce_form_field( 'country', array(
                    'label'=>'Country',
                    'class'=>array('form-row-wide','address-field','update_totals_on_change'),
                    'required' =>true,
                    'custom_attributes' => array('required' =>true),
                    'type'=>'country',
                    'autocomplete' => 'given-name'), 'US' );
                  woocommerce_form_field( 'address', array(
                    'label'=>'Address',
                    'class'=>array('form-row-wide','address-field'),
                    'required' =>true,
                    'custom_attributes' => array('required' =>true),
                    'autocomplete' => 'address-line1'), $department->address );
                  woocommerce_form_field( 'city', array(
                    'label'=>'City',
                    'class'=>array('form-row-wide','address-field'),
                    'required' =>true,
                    'custom_attributes' => array('required' =>true),
                    'autocomplete' =>'address-level2'), $department->city );
                  woocommerce_form_field( 'zip_code', array(
                    'label'=>'Zip',
                    'class'=>array('form-row-wide','address-field'),
                    'required' =>true,
                    'custom_attributes' => array('required' =>true),
                    'validate' => array('postcode'),
                    'autocomplete' => 'postal-code'), $department->zip_code );
                  woocommerce_form_field( 'state', array(
                    'label'=>'State',
                    'type'=>'state',
                    'class'=>array('form-row-wide','address-field'),
                    'required' =>true,
                    'custom_attributes' => array('required' =>true),
                    'validate' => array('state'),
                    'country_field' => 'country', 
                  ), $department->state );
                  woocommerce_form_field( 'note', array(
                    'label'=>'Note',
                    'type'=>'textarea',
                    'class'=>array('form-row-wide','address-field'),
                    'country_field' => 'country', 
                  ), $department->note );

            //    }
                ?>
              </div>

              <p>
                <button type="submit" class="button" name="save_address" value="<?php esc_attr_e( 'Save practice address', 'departments' ); ?>"><?php esc_html_e( 'Save address', 'departments' ); ?></button>
                <button type="button" class="cancel-update-button button" name="cancel" value="<?php esc_attr_e( 'Cancel', 'departments' ); ?>"><?php esc_html_e( 'Cancel', 'departments' ); ?></button>
                <?php wp_nonce_field( 'departments-edit_address', 'departments-edit-address-nonce' ); ?>
                <input type="hidden" name="department-action" value="edit_address" />
                <input type="hidden" name="department-id" value="<?=$department->address_id?>" />
              </p>
            </div>

            </form>

          </td>
        </tr>        
      <?php }?>
    <?php }else{?>
      <tr>
        <td colspan=3>No Practices</td>
      </tr>
    <?php }?>
  </tbody>
</table>
<script>
  jQuery(document).ready(function(){
    let previous_id;
    jQuery('.create-department').on('click',function(){
      jQuery('.add-department').show();
      jQuery('.create-department').hide();
      jQuery('.edit-department-department').hide();
      jQuery('.department-row').show();
    });
    jQuery('.cancel-add-button').on('click',function(){
      jQuery('.add-department').hide();
      jQuery('.create-department').show();
    });
    jQuery('.edit-department').on('click',function(){
      if(previous_id!=undefined){
        jQuery('#form'+previous_id).hide();
        jQuery('#department'+previous_id).show();
      }
      jQuery('#form'+this.dataset.id).show();
      jQuery('#department'+this.dataset.id).hide();
      jQuery('.add-department').hide();
      jQuery('.create-department').show();
      previous_id = this.dataset.id;
    });
    jQuery('.cancel-update-button').on('click',function(){
      jQuery('.edit-department-department').hide();
      jQuery('.department-row').show();
    });
    jQuery('.delete-department').on('click',function(){
      if(confirm('Can you confirm this department address')){
        jQuery('#add_department_action').val('delete_address');
        jQuery('#add_department_id').val(this.dataset.id);
        jQuery('#department-form').submit();
      }
    });
  });
</script>