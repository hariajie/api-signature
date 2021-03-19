<?php
function get_cuaca($param)
{
    $url = "https://signature.bmkg.go.id/api/signature/impact/public/list/" . $param;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    //curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $output = curl_exec($ch);
    curl_close($ch);

    //$json = file_get_contents($url);

    $result = json_decode($output, TRUE);
    $geojson = array(
        "type" => "FeatureCollection",
        "features" => array()
    );

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
    }

    return json_encode($geojson, JSON_PRETTY_PRINT);
}

echo get_cuaca($_GET["tgl"]);
