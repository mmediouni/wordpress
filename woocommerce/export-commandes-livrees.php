<?php 
function add_export_commandes_expediees_submenu() {
    add_submenu_page(
        'woocommerce',
        'Export Commandes Expediées',
        'Export Commandes Expediées',
        'manage_woocommerce', // Pour les roles qui ont la capabilité de gerer woocommerce (Admin , shop_manager, etc....)
        'export-commandes-expediees',
        'export_commandes_expediees_callback_view'
    );
}
$user = wp_get_current_user();
if ($user->ID == 10) { 
    add_action('admin_menu', 'add_export_commandes_expediees_submenu');
}
function export_commandes_expediees_callback_view() { ?>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>


    <div class="wrap">
        <h2>Exportation des commandes Expediées</h2>
        <table id="completed-orders-table" class="display table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Vendeur</th>
                    <th>Adresse Pickup</th>
                    <th>ID</th>
                    <th>Nom/Prénom</th>
                    <th>Téléphone</th>
                    <th>Adresse de Livraison</th>
                    <th>Ville</th>
                    <th>Total de la Commande</th>
                    <th>Moyen de Paiement</th>
                    <th>Commentaires</th>
                    <th>Transporteur</th>
                </tr>
            </thead>
            <tbody> <?php
                global $wpdb, $woocommerce;
                $args = array(
                    'post_type' => 'shop_order',
                    'post_status' => 'completed',
                    'posts_per_page' => -1,
                ); 
                $completed_orders = wc_get_orders($args);
                foreach ($completed_orders as $completed_order) {
                    $order_id = $completed_order->get_id();
                    $order = new WC_Order($order_id);
                    
                    $fullname_client = '';
                    $transporteur = '';
                    $ville_client = '';
                    $telephone_client = ''; 
                    $addresse_client = ''; 
                    $store_name = '';
                    $store_full_address = '';
                    $grand_tunis_cities = ['Tunis','Ben Arous','Ariana','Manouba', 'Mannouba', 'La Manouba', 'La Mannouba', 'BenArous']; // Editable
                    $allowed_transporter = ['Intigo','Mylerz Tunisie','Aramex Tunisie', 'Yassir']; // Editable
                    $ville_shipping_client = !empty($order->get_shipping_city()) ? $order->get_shipping_city() : '';
                    $ville_billing_client = !empty($order->get_billing_city()) ? $order->get_billing_city() : '';
                    if (!empty($ville_shipping_client)) {
                        $ville_client = $ville_shipping_client;
                    } elseif (!empty($ville_billing_client)) {
                        $ville_client = $ville_billing_client;
                    } else {
                        $ville_client = '';
                    }
                    $ville_client = strtolower($ville_client); // Convertir la ville du client en minuscules
                    $grand_tunis_cities = array_map('strtolower', $grand_tunis_cities);
                    
                    if (in_array($ville_client, $grand_tunis_cities)) {
                        $transporteur = get_post_meta($order_id, 'dn_multi_transporteur_selected_transporter', true);
                        $transporteur = strtolower($transporteur); // Convertir la ville du client en minuscules
                        $allowed_transporter = array_map('strtolower', $allowed_transporter);
                        if (in_array($transporteur, $allowed_transporter)) {
                            $notes = wc_get_order_notes(array('order_id' => $order_id));
                            $commentaires = '';
                            foreach ($notes as $note) {
                                if (strpos(strtolower($note->content), 'lv ') !== false) {
                                    $commentaires .= substr($note->content, strpos(strtolower($note->content), 'lv ') + 3) . ", ";
                                }
                            }
                            $fullname_billing_client = !empty($order->get_formatted_billing_full_name()) ? $order->get_formatted_billing_full_name() : '';
                            $fullname_shipping_client = !empty($order->get_formatted_shipping_full_name()) ? $order->get_formatted_shipping_full_name() : '';

                            if (!empty($fullname_shipping_client)) {
                                $fullname_client = $fullname_shipping_client;
                            } elseif (!empty($fullname_billing_client)) {
                                $fullname_client = $fullname_billing_client;
                            } else {
                                $fullname_client = '';
                            }

                            $ville_shipping_client = !empty($order->get_shipping_city()) ? $order->get_shipping_city() : '';
                            $ville_billing_client = !empty($order->get_billing_city()) ? $order->get_billing_city() : '';
                            if (!empty($ville_shipping_client)) {
                                $ville_client = $ville_shipping_client;
                            } elseif (!empty($ville_billing_client)) {
                                $ville_client = $ville_billing_client;
                            } else {
                                $ville_client = '';
                            }

                            $telephone_billing_client = !empty($order->get_billing_phone()) ? $order->get_billing_phone() : '';
                            $telephone_shipping_client = !empty($order->get_shipping_phone()) ? $order->get_shipping_phone() : '';

                            if (!empty($telephone_shipping_client)) {
                                $telephone_client = $telephone_shipping_client;
                            } elseif (!empty($telephone_billing_client)) {
                                $telephone_client = $telephone_billing_client;
                            } else {
                                $telephone_client = '';
                            }

                            $shipping_addresse_client_1 = $order->get_shipping_address_1();
                            $billing_addresse_client_1 = $order->get_billing_address_1();
                        
                            if (!empty($shipping_addresse_client_1)) {
                                $addresse_client1 = $shipping_addresse_client_1;
                            } elseif (!empty($billing_addresse_client_1)) {
                                $addresse_client1 = $billing_addresse_client_1;
                            } else {
                                $addresse_client1 = '';
                            }

                            $shipping_addresse_client_2 = $order->get_shipping_address_2();
                            $billing_addresse_client_2 = $order->get_billing_address_2();
                            if (!empty($shipping_addresse_client_2)) {
                                $addresse_client2 = $shipping_addresse_client_2;
                            } elseif (!empty($billing_addresse_client_2)) {
                                $addresse_client2 = $billing_addresse_client_2;
                            } else {
                                $addresse_client2 = '';
                            }
                            $addresse_client = $addresse_client1.' '.$addresse_client2 ;
                            
                            $total_de_la_commande = !empty($order->get_total()) ? $order->get_total() : '';
                            $moyen_de_paiement = !empty($order->get_payment_method_title()) ? $order->get_payment_method_title() : '';

                            $vendor_id = dokan_get_seller_id_by_order($order_id);
                            $get_vendor = get_user_meta($vendor_id);
                            if ($get_vendor != null) {
                                $store_name = $get_vendor['dokan_store_name'][0];
                                $dokan_profile_settings = unserialize($get_vendor['dokan_profile_settings'][0]);
                                if (isset($dokan_profile_settings['address'])) {
                                    $address_data = $dokan_profile_settings['address'];
                                    $store_full_address = $address_data['street_1'] . ', ' . $address_data['street_2'] . ', ' . $address_data['city'] . ', ' . $address_data['zip'];
                                }
                            }

                            echo '<tr>';
                            echo '<td>' . esc_html($store_name) . '</td>';
                            echo '<td>' . esc_html($store_full_address) . '</td>';
                            echo '<td>' . esc_html($order_id) . '</td>';
                            echo '<td>' . esc_html($fullname_client) . '</td>';
                            echo '<td>' . esc_html($telephone_client) . '</td>';
                            echo '<td>' . esc_html($addresse_client) . '</td>';
                            echo '<td>' . esc_html($ville_client) . '</td>';
                            echo '<td>' . esc_html($total_de_la_commande) . '</td>';
                            echo '<td>' . esc_html($moyen_de_paiement) . '</td>';
                            echo '<td>' . $commentaires . '</td>';
                            echo '<td>' . $transporteur . '</td>';
                            echo '</tr>';
                        }
                    }
                } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Vendeur</th>
                    <th>Adresse Pickup</th>
                    <th>ID</th>
                    <th>Nom/Prénom</th>
                    <th>Téléphone</th>
                    <th>Adresse de Livraison</th>
                    <th>Ville</th>
                    <th>Total de la Commande</th>
                    <th>Moyen de Paiement</th>
                    <th>Commentaires</th>
                    <th>Transporteur</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('#completed-orders-table').DataTable( {
                dom: 'Bfrtip',
                buttons: ['excel'],
                language: {
                    processing:     "Traitement en cours...",
                    search:         "Rechercher&nbsp;:",
                    lengthMenu:    "Afficher _MENU_ &eacute;l&eacute;ments",
                    info:           "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                    infoEmpty:      "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
                    infoFiltered:   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                    infoPostFix:    "",
                    loadingRecords: "Chargement en cours...",
                    zeroRecords:    "Aucun &eacute;l&eacute;ment &agrave; afficher",
                    emptyTable:     "Aucune donnée disponible dans le tableau",
                    paginate: {
                        first:      "Premier",
                        previous:   "Pr&eacute;c&eacute;dent",
                        next:       "Suivant",
                        last:       "Dernier"
                    },
                    aria: {
                        sortAscending:  ": activer pour trier la colonne par ordre croissant",
                        sortDescending: ": activer pour trier la colonne par ordre décroissant"
                    }
                }
            } );
        } );
    </script>
    <?php
}