<?php
add_filter( 'frm_pro_available_fields', 'add_pro_field' );
    function add_pro_field( $fields ) {
        $fields['wrw-cars'] = array(
            'name' => 'WRW Cars',
            'icon' => 'frm_icon_font frm_pencil_icon',
        );
        
        return $fields;
    }
    
    
    add_filter('frm_before_field_created', 'set_my_field_defaults');
    function set_my_field_defaults($field_data){
        if ( $field_data['type'] == 'wrw-cars' ) {
            $field_data['name'] = 'Wrw Car Length';

            $defaults = array(
                'value' => '0sqf',
            );

            foreach ( $defaults as $k => $v ) {
                $field_data['field_options'][ $k ] = $v;
            }
        }

        return $field_data;
    }

    add_action('frm_display_added_fields', 'show_the_admin_field');
    function show_the_admin_field($field){
        if ( $field['type'] != 'wrw-cars' ) {
            return;
        }
        $field_name = 'item_meta['. $field['id'] .']';
        ?>
            <p>This is a custom WRW Cars Field used for displaying the WRW Car Length Calculator on the frontend of the formidable forms, no configuration is needed asides making sure that Javascript is turned on from the form settings.</p>
            <?php
    }

    add_action('frm_form_fields', 'show_my_front_field', 10, 3);




    function show_my_front_field( $field, $field_name, $atts ) {
      if ( $field['type'] != 'wrw-cars' ) {
        return;
      }
        $car_makes = get_makes();
      $field['value'] = stripslashes_deep($field['value']);
        // Add the HTML for the custom field
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
                  // Add your JavaScript code here
                  var ImakeSelect = document.getElementById('car_make_select');
                  var ImodelSelect = document.getElementById('car_model_select');
                  var IyearSelect = document.getElementById('car_year_select');
                  var IroofSelect = document.getElementById('car_roof_select');

                  // Event listener for the model select
                  ImodelSelect.addEventListener('change', function () {
                      var selectedModel = ImodelSelect.options[ImodelSelect.selectedIndex].text;
                      //alert('Selected Model: ' + selectedModel);
                  });

                  // Add similar event listeners for other dropdowns as needed
              });
        </script>
        <script>
        jQuery(window).on('load', function() {
          
            var makes = [];
            var models = [];
            var years = [];
            var area_with_roof = "";
            var area_without_roof = ""

            //Get make from select_make
            jQuery('#car_make_select').on('change', function() {
                console.log('clicked car model select');
                car_reset_model_select();
                car_reset_year_select();
                car_reset_roof_select();
                jQuery("p.total-square-footage").remove();

                var makeSelected = jQuery(this).find("option:selected");
                var makeValueSelected = makeSelected.val();
                var makeTextSelected = makeSelected.text();

                //Add makeSelected value to #car_make_input input:text
                jQuery("#car_make_input input:text").val(makeValueSelected);
                                          console.log('selected a car model');
                jQuery.ajax({
                    type: "POST",
                    url: my_ajaxurl,
                    data: {
                        action: 'get_car_models',
                        car_make: makeValueSelected,
                    },
                    success: function(data) {
                            console.log('got here');
                            console.log(data);
                        jQuery("#car_model_select").prop("disabled", false);
                        jQuery("#car_model_select").append(data);
                        jQuery('#car_make_model_information_make input[type=text]').val(makeTextSelected);

                    },
                    error: function(errorThrown) {
                        console.log(errorThrown);
                    },
                    beforeSend: function() {
                        jQuery("#ajaxSpinner").show();
                    },
                    // hides the loader after completion of request, whether successfull or failor.
                    complete: function() {
                        jQuery("#ajaxSpinner").hide();
                    }
                });
            });

            //Get years from select_make and model
            jQuery('#car_model_select').on('change', function() {

                car_reset_year_select();
                car_reset_roof_select();

                var modelSelected = jQuery(this).find("option:selected");
                var modelValueSelected = modelSelected.val();
                var modelTextSelected = modelSelected.text();

                var makeSelected = jQuery("#car_make_select option:selected");
                var makeValueSelected = makeSelected.val();
                var makeTextSelected = makeSelected.text();

                //Add modelSelected value to #car_make_input input:text
                jQuery("#car_model_input input:text").val(modelValueSelected);

                jQuery.ajax({
                    type: "POST",
                    url: my_ajaxurl,
                    data: {
                        action: 'get_car_years',
                        car_make: makeValueSelected,
                        car_model: modelValueSelected
                    },
                    success: function(data) {
                        jQuery("#car_year_select").prop("disabled", false);
                        jQuery("#car_year_select").append(data);

                        jQuery('#car_make_model_information_model').val(modelTextSelected);

                    },
                    error: function(errorThrown) {
                        console.log(errorThrown);
                    },
                    beforeSend: function() {
                        jQuery("#ajaxSpinner").show();
                    },
                    // hides the loader after completion of request, whether successfull or failor.
                    complete: function() {
                        jQuery("#ajaxSpinner").hide();
                    }
                });
            });

            //Get years from select_make and model
            jQuery('#car_year_select').on('change', function() {

                car_reset_roof_select();

                var yearSelected = jQuery(this).find("option:selected");
                var yearValueSelected = yearSelected.val();
                var yearTextSelected = yearSelected.text();

                var makeSelected = jQuery("#car_make_select option:selected");
                var makeValueSelected = makeSelected.val();
                var makeTextSelected = makeSelected.text();

                var modelSelected = jQuery("#car_model_select option:selected");
                var modelValueSelected = modelSelected.val();
                var modelTextSelected = modelSelected.text();

                //Add yearSelected value to #car_year_input input:text
                jQuery("#car_year_input input:text").val(yearValueSelected);

                jQuery.ajax({
                    type: "POST",
                    url: my_ajaxurl,
                    data: {
                        action: 'get_car_area',
                        car_make: makeValueSelected,
                        car_model: modelValueSelected,
                        car_year: yearValueSelected
                    },
                    success: function(data) {
                        var data_array = data.split(',');

                        if (data_array[0] !== "0") {
                            // roof = yes
                            jQuery("#car_roof_select").removeClass("hidden");
                            jQuery("#car_roof_select option").remove();
                            jQuery("#car_roof_select").prop("disabled", false);
                            jQuery("#car_roof_select").append(data);
                            

                            jQuery('#car_make_model_information_year').val(yearTextSelected);

                             // Set value to #wrw-calculator based on selected roof type
                            

                            //Add makeSelected value to #car_make_input input:text
                            // jQuery("#car_roof_input input:text").val("Yes");

                        } else if (data_array[0] == "0") {
                            //roof = no
                            car_reset_roof_select();
                            jQuery("#car_roof_select").addClass("hidden");
                            var squareRootOfArea = Number(Math.sqrt(data_array[1]) * 12);
                            jQuery("<p class=\"total-square-footage\"><strong>Total Sq. Ft.:</strong> " + squareRootOfArea.toPrecision(4) + "</p>").insertAfter("select#car_roof_select");
                            // jQuery("#car_roof_input input:text").val("No");

                             // Set value to #wrw-calculator when there is no roof
                            jQuery('#wrw-calculator').val(squareRootOfArea.toPrecision(4));

                        };

                    },
                    error: function(errorThrown) {
                        console.log(errorThrown);
                    },
                    beforeSend: function() {
                        jQuery("#ajaxSpinner").show();
                    },
                    // hides the loader after completion of request, whether successfull or failor.
                    complete: function() {
                        jQuery("#ajaxSpinner").hide();
                    }
                });
            });

            jQuery('#car_roof_select').on('change', function() {
                var selectedRoofType = jQuery("#car_roof_select option:selected").text();
                $('#wrw-calculator').val(selectedRoofType);


            });

            jQuery("#total_area_input .tmcp-textfield").keyup(function(event) {
                var total_area = jQuery(this).val();
                var total_area_sq_ft = Math.sqrt(total_area) * 12;
                jQuery('#length_needed').val(total_area_sq_ft);
                jQuery('#width_needed').val(total_area_sq_ft);
                jQuery('form.cart').trigger("wc-measurement-price-calculator-update");
            });

        

            /**
             *
             * Clear product calculator
             *
             */

            function clearProductCalculator() {
                //Clear WxH inputs
                jQuery('#length_needed').val('0');
                jQuery('#width_needed').val('0');

                jQuery('form.cart').trigger("wc-measurement-price-calculator-update");
            }

            /**
             *
             * Clear WxH inputs and select fields
             *
             */

            function reset_all_order_inputs() {
                //Clear WxH inputs
                jQuery('#length_needed').val('0');
                jQuery('#width_needed').val('0');

                //Clear car make/model information inputs (EPO fields)
                jQuery("#car_make_input input:text").val("");
                jQuery("#car_model_input input:text").val("");
                jQuery("#car_year_input input:text").val("");
                jQuery("#car_roof_input input:text").val("");

                //Clear make, model, tear dropdowns
                jQuery("#car_make_select").val(jQuery("#car_make_select option:first").val()).change();

                jQuery("#car_model_select").empty();
                jQuery("#car_model_select").append('<option value="">Select Model</option>');
                jQuery("#car_model_select").prop("disabled", true);

                jQuery("#car_year_select").empty();
                jQuery("#car_year_select").prop("disabled", true);
                jQuery("#car_year_select").append('<option value="">Select Year</option>');

                jQuery("#car_roof_select").empty();
                jQuery("#car_roof_select").prop("disabled", true);
                jQuery("#car_roof_select").append('<option value="">Select Roof Option</option>');
                jQuery("#car_roof_select").addClass("hidden");

                //Clear total area input
                jQuery('#total_area_input input').val('');

                //Remove total square foot paragraph tag
                jQuery("p.total-square-footage").remove();

                jQuery('form.cart').trigger("wc-measurement-price-calculator-update");
            }

                          /**
                           *
                           * Make Model Order Inputs Resets
                           *
                           */

                          function car_reset_model_select() {
                              jQuery("#model_select option").remove();
                              jQuery("#model_select").append('<option value="">Select Model</option>');
                              jQuery("#model_select").prop("disabled", true);
                              jQuery("p.total-square-footage").remove();

                              //Clear WxH inputs
                              jQuery('#length_needed').val('0');
                              jQuery('#width_needed').val('0');

                              jQuery('form.cart').trigger("wc-measurement-price-calculator-update");
                          }


        /**
         *
         * Make Model Order Inputs Resets
         *
         */

        function car_roof_select() {
            jQuery("#car_model_select option").remove();
            jQuery("#car_model_select").append('<option value="">Select Model</option>');
            jQuery("#car_model_select").prop("disabled", true);
            jQuery("p.total-square-footage").remove();

            //Clear WxH inputs
            jQuery('#length_needed').val('0');
            jQuery('#width_needed').val('0');

            jQuery('form.cart').trigger("wc-measurement-price-calculator-update");
        }

        function car_reset_year_select() {
            jQuery("#car_year_select option").remove();
            jQuery("#car_year_select").append('<option value="">Select Year</option>');
            jQuery("#car_year_select").prop("disabled", true);
            jQuery("p.total-square-footage").remove();

            //Clear WxH inputs
            jQuery('#length_needed').val('0');
            jQuery('#width_needed').val('0');

            jQuery('form.cart').trigger("wc-measurement-price-calculator-update");
        }

        function car_reset_roof_select() {
            jQuery("#car_roof_select option").remove();
            jQuery("#car_roof_select").append('<option value="">Select Roof Option</option>');
            jQuery("#car_roof_select").prop("disabled", true);
            jQuery("#car_roof_select").addClass("hidden");

            //Clear WxH inputs
            jQuery('#length_needed').val('0');
            jQuery('#width_needed').val('0');

            jQuery('form.cart').trigger("wc-measurement-price-calculator-update");
        } });

      
        </script>
        <?php
        
            $custom_field = '
            <section class="ordering-methods-wrapper">
                <section class="ordering-methods">
                    <a href="#" class="order-method order-method-make-model active" data-active-ordering-method=".order-method-make-model-wrapper">
                        <i class="flaticon-car"></i><span>Enter Make/Model</span>
                    </a>
                 
                 
                </section>
                <section class="order-method-make-model-wrapper">
                    <div id="ajaxSpinner" style="display: none;"><div class="loader"></div></div>
                    <div class="frm_primary_label">What is your vehicle make?</div>
                    <select id="car_make_select" required class="mb-5">
                        <option value="">Select Make</option>';

            foreach ($car_makes as $car_make) {
                $custom_field .= '<option value="' . $car_make . '">' . $car_make . '</option>';
            };

            $custom_field .= '
                    </select>
                    <div class="frm_primary_label">What model is your vehicle?</div>
                    <select id="car_model_select" required disabled class="mb-5">
                        <option value="">Select Model</option>
                    </select>
                    <div class="frm_primary_label">What year is your vehicle?</div>
                    <select id="car_year_select" disabled class="mb-5">
                        <option value="">Select Year</option>
                    </select>
                    <select id="car_roof_select" class="hidden" disabled class="mb-5">
                        <option value="">Select Roof Option</option>
                        <option value="">With Roof</option>
                        <option value="">Without Roof</option>
                    </select>
                </section>
            </section>';
        
      echo $custom_field;
      ?>
<input type="text" readonly id="wrw-calculator" name="<?php echo esc_attr( $field_name ) ?>" value="<?php echo esc_attr($field['value']) ?>" />
<?php
        
    
    }