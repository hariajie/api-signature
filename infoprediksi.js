let table = {};

function init_map() {
  let map = L.map("mapid", { minZoom: 5 }).setView(
    [-1.2828926, 119.1433416],
    5
  );

  //Siapin variable besok
  let date = new Date();
  // Add a day
  let besok = date.setDate(date.getDate() + 1);
  let tgl_besok = date.toISOString().split("T")[0];
  besok = tgl_besok + "T00:00:00.000Z";
  let geojsonLayer;

  L.tileLayer.provider("GoogleHybrid").addTo(map);

  $.ajax({
    url: "https://localhost/api-signature/api_signature.php?tgl=" + besok,
    method: "GET",
    dataType: "json",
    beforeSend: function () {
      $("#loading").show();
    },
    success: function (response) {
      console.log(response);
      let geojson = response.geojson;
      let geoJsonLayer = new L.GeoJSON(geojson, {
        style: function (feature) {
          let cat = feature.properties.category;
          let warna, style;
          cat = parseInt(cat);
          //console.log("Cate :" + cat)

          if (cat == 10) {
            warna = "#db0000";
          } else if (cat > 5) {
            warna = "#f09835";
          } else if (cat > 0) {
            warna = "#fff157";
          }

          // console.log(warna);

          style = {
            color: warna,
            weight: 0,
            opacity: 0.7,
            fillOpacity: 0.7,
          };

          return style;
        },
        onEachFeature: function (feature, layer) {
          //Bikin list kab
          let impact = feature.properties.impacted;
          console.log("Impact");
          console.log(Object.keys(impact));
          let tmp = {};
          for (const [key, val] of Object.entries(impact)) {
            tmp[key] = Object.keys(val);
            // for (const [k, v] of Object.entries(impact[key])) {
            //   tmp[key].push(k);
            // }
          }
          let list = {
            prov: tmp,
            status: feature.properties.status,
          };

          let sta;
          if (list.status == "Awas") {
            sta = "<span class='badge awas text-dark'>Awas</span>";
          } else if (list.status == "Siaga") {
            sta = "<span class='badge siaga text-dark'>Siaga</span>";
          } else if (list.status == "Waspada") {
            sta = "<span class='badge waspada text-dark'>Waspada</span>";
          }

          let content =
            "<div class='fs-6'><b>Status :<b></b> " + sta + "<br><br>";
          content +=
            "<span class='fs-6 fw-light'><b>Wilayah Terdampak</b></span>";
          content +=
            "<table class='table table-sm table-responsive table-bordered'>";
          content += "<thead class='bg-info fw-bold'>";
          content += "<tr><td>Provinsi</td><td>Kab/Kota</td></tr></thead>";
          content += "<tbody>";
          for (const [key, val] of Object.entries(list.prov)) {
            content += "<tr><td>" + key + "</td><td>";
            for (const [k, v] of Object.entries(list.prov[key])) {
              content += v + "<br>";
            }
            content += "</td></tr>";
          }
          content += "</tbody>";
          content += "</table>";
          content += "</div>";
          let popup = L.popup().setContent(content);
          layer.bindPopup(popup, { minWidth: 350 });
        },
      }).addTo(map);
      $("#loading").hide();
      table = response.data;
      list_table(table);
    },
  });
  $(".tanggal").html("tanggal " + "<b>" + format_tanggal(tgl_besok) + "</b>");
}

function list_table(table) {
  let html = "<table class='table table-sm table-responsive table-bordered'>";
  html += "<thead class='bg-primary text-white fw-bold'>";
  html += "<tr><td>Status</td><td>Provinsi</td></tr></thead>";
  html += "<tbody>";
  if (Object.keys(table.awas).length > 0) {
    html += "<tr class='awas'><td>Awas</td>";
    html += "<td>";
    for (const [key, val] of Object.entries(table.awas)) {
      html += val + "<br>";
    }
    html += "</td></tr>";
  }
  if (Object.keys(table.siaga).length > 0) {
    html += "<tr class='siaga'><td>Siaga</td>";
    html += "<td>";
    for (const [key, val] of Object.entries(table.siaga)) {
      html += val + "<br>";
    }
    html += "</td></tr>";
  }
  if (Object.keys(table.waspada).length > 0) {
    html += "<tr class='waspada'><td>Waspada</td>";
    html += "<td >";
    for (const [key, val] of Object.entries(table.waspada)) {
      html += val + "<br>";
    }
    html += "</td></tr>";
  }
  if (
    Object.keys(table.waspada).length == 0 &&
    Object.keys(table.awas).length == 0 &&
    Object.keys(table.siaga).length == 0
  ) {
    html += "<tr class='text-center'><td colspan='2'>Tidak ada data.</td>";
    html += "</td></tr>";
  }
  html += "</tbody>";
  html += "</table>";

  $(".prov-list").html(html);
}

init_map();
