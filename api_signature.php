<?php
function urutinProv($array, $strict = null)
{
    $orderArray = array(
        "Aceh",
        "Sumatera Utara",
        "Sumatera Barat",
        "Riau",
        "Jambi",
        "Sumatera Selatan",
        "Bengkulu",
        "Lampung",
        "Kepulauan Bangka Belitung",
        "Kepulauan Riau",
        "Dki Jakarta",
        "Jawa Barat",
        "Jawa Tengah",
        "Daerah Istimewa Yogyakarta",
        "Jawa Timur",
        "Banten",
        "Bali",
        "Nusa Tenggara Barat",
        "Nusa Tenggara Timur",
        "Kalimantan Barat",
        "Kalimantan Tengah",
        "Kalimantan Selatan",
        "Kalimantan Timur",
        "Kalimantan Utara",
        "Sulawesi Utara",
        "Sulawesi Tengah",
        "Sulawesi Selatan",
        "Sulawesi Tenggara",
        "Gorontalo",
        "Sulawesi Barat",
        "Maluku",
        "Maluku Utara",
        "Papua",
        "Papua Barat"
    );

    if (!empty($array) && !empty($orderArray)) {
        $ordered = [];
        foreach ($orderArray as $item) {
            $search = !is_null($strict) ? array_keys($array, $item, $strict) : array_keys(preg_grep('#' . $item . '#', $array));
            if (!empty($search)) {
                foreach ($search as $key) {
                    $ordered[$key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }

        return (object) array_merge($ordered, $array);
    } else {
        return array();
    }
}

function get_cuaca($param)
{
    $url = "https://signature.bmkg.go.id/api/signature/impact/public/list/" . $param;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $output = curl_exec($ch);
    curl_close($ch);

    //$json = file_get_contents($url);

    $result = json_decode($output, TRUE);
    $geojson = array(
        "type" => "FeatureCollection",
        "features" => array()
    );

    //Array ke view
    $resp = array("data" => array("awas" => array(), "siaga" => array(), "waspada" => array()), "geojson" => array());
    $tmp_awas = array();
    $tmp_siaga = array();
    $tmp_waspada = array();

    //Convert to geoJSON

    foreach ($result["data"] as $row) {
        $cat = intval($row["area"]["properties"]["category"]);
        if ($cat == 10) {
            $status = "Awas";
        } else if ($cat > 5) {
            $status = "Siaga";
        } else if ($cat > 0) {
            $status = "Waspada";
        }

        $push = array(
            "type" => "Feature",
            "geometry" => array(
                "type" => $row["area"]["geometry"]["type"],
                "coordinates" => $row["area"]["geometry"]["coordinates"]
            ),
            "properties" => array(
                "category" => $cat,
                "impacted" => $row["impacted"],
                "status" => $status
            )
        );
        array_push($geojson["features"], $push);

        //Insert Data

        if ($status == "Awas") {
            foreach (array_keys($row["impacted"]) as $val) {
                $tmp_awas[] = $val;
            }
        } else if ($status == "Siaga") {
            foreach (array_keys($row["impacted"]) as $val) {
                $tmp_siaga[] = $val;
            }
        } else if ($status == "Waspada") {
            foreach (array_keys($row["impacted"]) as $val) {
                $tmp_waspada[] = $val;
            }
        }
    }

    //Biar ga redundan
    foreach ($tmp_awas as $awas) {
        array_push($resp["data"]["awas"], $awas);
    }
    foreach ($tmp_siaga as $siaga) {
        array_push($resp["data"]["siaga"], $siaga);
    }
    foreach ($tmp_waspada as $wasp) {
        array_push($resp["data"]["waspada"], $wasp);
    }

    $resp["data"]["awas"] = urutinProv(array_unique($resp["data"]["awas"]), true);
    $resp["data"]["siaga"] = urutinProv(array_unique($resp["data"]["siaga"]), true);
    $resp["data"]["waspada"] = urutinProv(array_unique($resp["data"]["waspada"]), true);


    $resp["geojson"] = $geojson;

    return json_encode($resp, JSON_PRETTY_PRINT);
}

echo get_cuaca($_GET["tgl"]);
