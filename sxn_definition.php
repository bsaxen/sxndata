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
define("SXN_DATABASE_ADMIN"                 , "nb_db_admin"     , true);

//Users Table
define("SXN_ADMIN_TABLE_USERS"              , "nb_users"     , true);
define("SXN_ADMIN_USERS_COLUMN_UID"         , "nb_uid"       , true);
define("SXN_ADMIN_USERS_COLUMN_NAME"        , "nb_username"  , true);
define("SXN_ADMIN_USERS_COLUMN_PASSWORD"    , "nb_password"  , true);

//Streams Table
define("SXN_ADMIN_TABLE_STREAMS"              , "nb_streams"     , true);
define("SXN_ADMIN_STREAMS_COLUMN_SID"         , "nb_sid"         , true);
define("SXN_ADMIN_STREAMS_COLUMN_TYPE"        , "nb_data_type"   , true);
define("SXN_ADMIN_STREAMS_COLUMN_UNIT"        , "nb_unit"        , true);
define("SXN_ADMIN_STREAMS_COLUMN_TITLE"       , "nb_title"          , true);
define("SXN_ADMIN_STREAMS_COLUMN_TAG"         , "nb_tag"            , true);
define("SXN_ADMIN_STREAMS_COLUMN_DESCRIPTION" , "nb_description"    , true);
define("SXN_ADMIN_STREAMS_COLUMN_OWNERUID"    , "nb_owner_uid"      , true);
define("SXN_ADMIN_STREAMS_COLUMN_PERMISSION"  , "nb_permission"     , true);

//Data Types Table
define("SXN_ADMIN_TABLE_DATATYPES"              , "nb_data_types"     , true);
define("SXN_ADMIN_DATATYPES_COLUMN_NAME"        , "nb_name"              , true);
define("SXN_ADMIN_DATATYPES_COLUMN_UNIT"        , "nb_unit"              , true);

//Command Types Table
define("SXN_ADMIN_TABLE_COMMANDTYPES"              , "nb_command_types"    , true);
define("SXN_ADMIN_COMMANDTYPES_COLUMN_NAME"        , "nb_command"          , true);
define("SXN_ADMIN_COMMANDTYPES_COLUMN_DESCRIPTION" , "nb_description"      , true);

//Application Table
define("SXN_ADMIN_TABLE_APPLICATIONS"              , "nb_applications"  , true);
define("SXN_ADMIN_APPLICATIONS_COLUMN_TITLE"       , "title"            , true);
define("SXN_ADMIN_APPLICATIONS_COLUMN_OWNERID"     , "ownerId"          , true);
define("SXN_ADMIN_APPLICATIONS_COLUMN_SHARED"      , "shared"           , true);
define("SXN_ADMIN_APPLICATIONS_COLUMN_CHARTTYPE"   , "chartType"        , true);

//App stream link Table
define("SXN_ADMIN_TABLE_APPSTREAMLINK"             , "nb_application_stream_link" , true);
define("SXN_ADMIN_APPSTREAMLINK_COLUMN_APPID"      , "appid"         , true);
define("SXN_ADMIN_APPSTREAMLINK_COLUMN_SID"        , "sid"      , true);

//------------------------------------------------------------------------------
// Control database
//------------------------------------------------------------------------------
define("SXN_DATABASE_CONTROL"                  , "nb_db_control"  , true);

//Command Table
define("SXN_CONTROL_TABLE_COMMANDS"            , "nb_control"   , true);
define("SXN_CONTROL_COMMANDS_COLUMN_SID"       , "stream_id"    , true);
define("SXN_CONTROL_COMMANDS_COLUMN_COMMAND"   , "command_id"   , true);
define("SXN_CONTROL_COMMANDS_COLUMN_STATUS"    , "status"       , true);

//------------------------------------------------------------------------------
// Collector database
//------------------------------------------------------------------------------
define("SXN_DATABASE_COLLECTOR"     , "nb_db_collector"     , true);

//Data Table
define("SXN_COLLECTOR_TABLE_DATA_PREFIX", "nb_data_"      , true);
define("SXN_COLLECTOR_DATA_COLUMN_VALUE", "nb_value"         , true);
?>
