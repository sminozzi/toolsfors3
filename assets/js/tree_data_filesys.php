<?php
ini_set("memory_limit", "512M");
set_time_limit(600);
global $toolsfors3_dir_for_search;
$toolsfors3_dir_for_search = getcwd() . "/";
if (!defined("ABSPATH")) {
    define("ABSPATH", "");
}
$toolsfors3_dir_for_search = ABSPATH . "/";
$toolsfors3_dir_for_search = TOOLSFORS3PATH; // . '/';
$toolsfors3_data_filesys = toolsfors3_fetch_files($toolsfors3_dir_for_search);
// CREATE NODES //////////////////
$i = 0;
foreach ($toolsfors3_data_filesys as $key => $item) {
    if ($item["parent"] == "-1") {
        continue;
    }
    if (!isset($item["parent"])) {
        die("fail L 88");
    }
    $toolsfors3_data_filesys[$item["parent"]]["nodes"][] = $item["path"];
    continue;
}
//  CREATE JSON ////////////////////////
function toolsfors3_create_json_filesys($indice_node, $toolsfors3_data_filesys)
{
    global $json;
    global $j;
    $columns = array_column($toolsfors3_data_filesys, "path");
    array_multisort($columns, SORT_ASC, $toolsfors3_data_filesys);
    $tot = count($toolsfors3_data_filesys);
    $ctd = 0;
    $json = '[{"text":"Root","icon":"","nodes":[';
    for ($i = 0; $i < $tot; $i++) {
        $ctd++;
        if ($toolsfors3_data_filesys[$i]["parent"] !== "-1") {
            continue;
        }
        $item = trim($toolsfors3_data_filesys[$i]["path"]);
        if (empty($item)) {
            continue;
        }
        $json .= "{";
        $json .= '"text":"';
        $json .= $item;
        $json .= '","icon":""';
        // extra nodes
        $indice_node = array_search(
            $item,
            array_column($toolsfors3_data_filesys, "path"),
            true
        );
        if (isset($toolsfors3_data_filesys[$indice_node]["nodes"])) {
            $json = s3transfer_create_nodes(
                $indice_node,
                $toolsfors3_data_filesys
            );
        }
        $json .= "}";
        if ($ctd < $tot - 0) {
            $json .= ",";
        }
    } // end for
    while (substr($json, -1) == ",") {
        //if(substr($json, -1) == ',') {
        $json = substr($json, 0, strlen($json) - 1);
    }
    $json .= "]"; // end node
    $json .= "}";
    $json .= "]"; // end MAIN node
    return $json;
} // end function Create Json
if (!isset($indice_node)) {
    $indice_node = "-1";
}
$json = toolsfors3_create_json_filesys($indice_node, $toolsfors3_data_filesys);
// $json='[{"text":"Inbox","icon":"","nodes":[{"text":"Office","icon":"fa fa-inbox","nodes":[{"icon":"fa fa-inbox","text":"Customers"},{"icon":"fa fa-inbox","text":"Co-Workers"}]},{"icon":"fa fa-inbox","text":"Others"}]},{"icon":"fa fa-archive","text":"Drafts"},{"icon":"fa fa-calendar","text":"Calendar"},{"icon":"fa fa-address-book","text":"Contacts"},{"icon":"fa fa-trash","text":"Deleted Items"}]';
$json = str_replace("<br>", "", $json);
$json = str_replace(["\n", "\r\n", "\r", "\t"], "", $json);
die($json);
/*         FUNCTION  CREATE NODES            */
function s3transfer_create_nodes($indice_node, $toolsfors3_data_filesys)
{
    // tem q achar ela na main array e ver se tem nodes...
    global $json;
    if (isset($toolsfors3_data_filesys[$indice_node]["nodes"])) {
        $toolsfors3_data_filesys3 =
            $toolsfors3_data_filesys[$indice_node]["nodes"];
        $tot3 = count($toolsfors3_data_filesys3);
        if ($tot3 > 0) {
            $json .= ',"nodes": [';
        }
        for ($k = 0; $k < $tot3; $k++) {
            $item3 = trim($toolsfors3_data_filesys3[$k]);
            $json .=
                ' {
            "icon": "",
            "text": "' .
                $item3 .
                '"';
            $indice_node_node = array_search(
                $item3,
                array_column($toolsfors3_data_filesys, "path"),
                true
            );
            if (isset($toolsfors3_data_filesys[$indice_node_node]["nodes"])) {
                // Node has node
                $json = s3transfer_create_nodes(
                    $indice_node_node,
                    $toolsfors3_data_filesys
                );
            }
            $json .= "}";
            if ($k < $tot3 - 1) {
                $json .= ",";
            }
        } //  end for
        if ($tot3 > 0) {
            $json .= "]";
        }
    } // end if tem nodes
    return $json;
} // end function
function toolsfors3_fetch_files($dir)
{
    global $toolsfors3_filesys_result;
    global $toolsfors3_dir_for_search;
    $i = 0;
    $x = scandir($dir);
    if (!isset($toolsfors3_filesys_result)) {
        $toolsfors3_filesys_result = [];
    }
    foreach ($x as $filename) {
        if ($filename == ".") {
            continue;
        }
        if ($filename == "..") {
            continue;
        }
        $filePath = $dir . $filename;
        if (!is_dir($filePath)) {
            continue;
        }
        if (empty($filePath)) {
            continue;
        }
        if (is_dir($filePath)) {
            if ($i == 0) {
                // Novo parente.
                $parent = $dir;
                $parent_for_search = trim(substr($dir, 0, strlen($dir) - 1));
                if (
                    $parent_for_search ==
                    substr(
                        $toolsfors3_dir_for_search,
                        0,
                        strlen($toolsfors3_dir_for_search) - 1
                    )
                ) {
                    $indice_parent = "-1";
                } else {
                    if (
                        gettype(count($toolsfors3_filesys_result)) ==
                            "integer" and
                        count($toolsfors3_filesys_result) > 0
                    ) {
                        $indice_parent = array_search(
                            $parent_for_search,
                            array_column($toolsfors3_filesys_result, "path"),
                            true
                        );
                        if ($indice_parent === false) {
                            // Bill
                            if (count($toolsfors3_filesys_result) == 0) {
                                $indice_parent;
                            } else {
                                die("NOT FOUD !!!!");
                            }
                        }
                        $indice_parent = array_search(
                            $parent_for_search,
                            array_column($toolsfors3_filesys_result, "path"),
                            true
                        );
                    } else {
                        $indice_parent = 0;
                    }
                }
            } // end I = 0
            $ctd = count($toolsfors3_filesys_result);
            $toolsfors3_filesys_result[] = [
                "path" => trim($filePath),
                "parent" => $indice_parent,
            ];
            $i++;
            $filePath = $dir . $filename . "/";
            foreach (toolsfors3_fetch_files($filePath) as $childFilename) {
                if (gettype($childFilename) === "object") {
                    continue;
                }
                if (!isset($childFilename[0])) {
                    continue;
                }
                if ($childFilename[0] == ".") {
                    continue;
                }
                if ($childFilename[0] == "..") {
                    continue;
                }
                $filePath2 = $dir . $childFilename[0];
                if (!is_dir($filePath2)) {
                    continue;
                }
                if (empty($filePath2)) {
                    continue;
                }
                $ctd = count($toolsfors3_filesys_result);
                try {
                    $toolsfors3_filesys_result[] = [
                        "path" => trim($filePath2),
                        "parent" => "999",
                    ];
                    $i++;
                } catch (Exception $e) {
                    echo "Message: " . esc_attr($e->getMessage());
                }
            }
        } // end isdir
    } // end for
    return $toolsfors3_filesys_result;
} // end function
