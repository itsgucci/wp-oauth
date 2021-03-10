<?php

class Mo_OAuth_Client_Admin_Attribute_Mapping {
    protected static $attributes;
    protected static function initialize_vars() {
        self::$attributes = get_option( 'mo_oauth_attr_name_list' );
    }

    private static function emit_css() {
        ?>
        <style>.mo-side-table{border-collapse:collapse;}.mo-side-table-th {background-color: #eee; text-align: center; padding: 8px; border-width:1px; border-style:solid; border-color:#212121;}.mo-side-table-tr:nth-child(odd) {background-color: #f2f2f2;} .mo-side-table-td{padding:8px;border-width:1px; border-style:solid; border-color:#212121;}.attr-list-div{padding: 10px 0px;}</style>
        <?php
    }
    public static function emit_attribute_table() {
        self::initialize_vars();
        if ( false === self::$attributes || ! is_array( self::$attributes ) ) {
            return;
        }
        self::emit_css();
        ?>
        <div class="attr-list-div">
            <div id="mo_support_layout" class="mo_support_layout">
                <div class="attr-list-div">
                    <h2>Test Configuration</h2>
                    <table class="mo-side-table">
                        <tr class="mo-side-table-tr">
                            <th class="mo-side-table-th">Attribute Name</th>
                            <th class="mo-side-table-th">Attribute Value</th>
                        </tr>
                        <?php mo_oauth_client_testattrmappingconfig( '', self::$attributes, 'mo-side-table-' ); ?>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}