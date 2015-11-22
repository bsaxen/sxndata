<?php
//==============================================================================
//Definitions
//==============================================================================

define("SXN_USER"                     , "root"     , true);
define("SXN_PASSWORD"                 , "amazon"   , true);

define("SXN_GENERAL_COLUMN_ID"          , "id" , true);
define("SXN_GENERAL_COLUMN_TIMESTAMP"   , "ts" , true);
define("SXN_GENERAL_DISTINCT_DATES"     , "d_dates" , true);

//------------------------------------------------------------------------------
// Uplink Messages (MID)
//------------------------------------------------------------------------------
define("SXN_DATA"              , 1     , true);
define("SXN_LATEST"            , 2     , true);
define("SXN_MAILBOX"           , 3     , true);
//------------------------------------------------------------------------------
// Admin database
//------------------------------------------------------------------------------
define("SXN_DATABASE_ADMIN"                 , "SXN_db_admin"     , true);

//Users Table
define("SXN_ADMIN_TABLE_USERS"              , "SXN_users"     , true);
define("SXN_ADMIN_USERS_COLUMN_UID"         , "SXN_uid"       , true);
define("SXN_ADMIN_USERS_COLUMN_NAME"        , "SXN_username"  , true);
define("SXN_ADMIN_USERS_COLUMN_PASSWORD"    , "SXN_password"  , true);

//Streams Table
define("SXN_ADMIN_TABLE_STREAMS"              , "SXN_streams"     , true);
define("SXN_ADMIN_STREAMS_COLUMN_SID"         , "SXN_sid"         , true);
define("SXN_ADMIN_STREAMS_COLUMN_TYPE"        , "SXN_data_type"   , true);
define("SXN_ADMIN_STREAMS_COLUMN_UNIT"        , "SXN_unit"        , true);
define("SXN_ADMIN_STREAMS_COLUMN_TITLE"       , "SXN_title"          , true);
define("SXN_ADMIN_STREAMS_COLUMN_TAG"         , "SXN_tag"            , true);
define("SXN_ADMIN_STREAMS_COLUMN_DESCRIPTION" , "SXN_description"    , true);
define("SXN_ADMIN_STREAMS_COLUMN_OWNERUID"    , "SXN_owner_uid"      , true);
define("SXN_ADMIN_STREAMS_COLUMN_PERMISSION"  , "SXN_permission"     , true);

//Data Types Table
define("SXN_ADMIN_TABLE_DATATYPES"              , "SXN_data_types"     , true);
define("SXN_ADMIN_DATATYPES_COLUMN_NAME"        , "SXN_name"              , true);
define("SXN_ADMIN_DATATYPES_COLUMN_UNIT"        , "SXN_unit"              , true);

//Command Types Table
define("SXN_ADMIN_TABLE_COMMANDTYPES"              , "SXN_command_types"    , true);
define("SXN_ADMIN_COMMANDTYPES_COLUMN_NAME"        , "SXN_command"          , true);
define("SXN_ADMIN_COMMANDTYPES_COLUMN_DESCRIPTION" , "SXN_description"      , true);

//Application Table
define("SXN_ADMIN_TABLE_APPLICATIONS"              , "SXN_applications"  , true);
define("SXN_ADMIN_APPLICATIONS_COLUMN_TITLE"       , "title"            , true);
define("SXN_ADMIN_APPLICATIONS_COLUMN_OWNERID"     , "ownerId"          , true);
define("SXN_ADMIN_APPLICATIONS_COLUMN_SHARED"      , "shared"           , true);
define("SXN_ADMIN_APPLICATIONS_COLUMN_CHARTTYPE"   , "chartType"        , true);

//App stream link Table
define("SXN_ADMIN_TABLE_APPSTREAMLINK"             , "SXN_application_stream_link" , true);
define("SXN_ADMIN_APPSTREAMLINK_COLUMN_APPID"      , "appid"         , true);
define("SXN_ADMIN_APPSTREAMLINK_COLUMN_SID"        , "sid"      , true);

//------------------------------------------------------------------------------
// Control database
//------------------------------------------------------------------------------
define("SXN_DATABASE_CONTROL"                  , "SXN_db_control"  , true);

//Command Table
define("SXN_CONTROL_TABLE_COMMANDS"            , "SXN_control"   , true);
define("SXN_CONTROL_COMMANDS_COLUMN_SID"       , "stream_id"    , true);
define("SXN_CONTROL_COMMANDS_COLUMN_COMMAND"   , "command_id"   , true);
define("SXN_CONTROL_COMMANDS_COLUMN_STATUS"    , "status"       , true);

//------------------------------------------------------------------------------
// Collector database
//------------------------------------------------------------------------------
define("SXN_DATABASE_COLLECTOR"     , "SXN_db_collector"     , true);

//Data Table
define("SXN_COLLECTOR_TABLE_DATA_PREFIX", "SXN_data_"      , true);
define("SXN_COLLECTOR_DATA_COLUMN_VALUE", "SXN_value"         , true);
?>
