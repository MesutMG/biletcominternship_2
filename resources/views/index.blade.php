<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>ogrenci tablo</title>
</head>
<style></style>

<body>
  
  <div id="TABLOID" class="TABLOID">
    <table>
        <tr id="sorting-table">
            <td style="border: 0;"></td>
            <th data-id="0" class="sortBtn">ID</th>
            <th data-id="1" class="sortBtn">AD</th>
            <th data-id="2" class="sortBtn">SOYAD</th>
            <th data-id="3" class="sortBtn">NO</th>
            <th data-id="4" class="sortBtn">BOLUM</th>
            <th data-id="5" class="sortBtn">YAS</th>
            <td style="border: 0;"></td>
        </tr>
        <tr>
            <td style="border: 0;"></td>
            <td><input type="text" placeholder="Ara: ID" id="idfilter" class="filter"></td>
            <td><input type="text" placeholder="Ara: AD" id="adfilter" class="filter"></td>
            <td><input type="text" placeholder="Ara: SOYAD" id="soyadfilter" class="filter"></td>
            <td><input type="text" placeholder="Ara: NO" id="nofilter" class="filter"></td>
            <td><input type="text" placeholder="Ara: BOLUM" id="bolumfilter" class="filter"></td>
            <td><input type="text" placeholder="Ara: YAS" id="yasfilter" class="filter"></td>
            <td style="border: 0;"><button type="submit" id="filtreleBtn" class="filter">Search</button></td>
        </tr>
      </table>
      <div id="insideTable"></div>
  </div>

  <div id="pagination" class="pagination"></div>

  <div>
  <label for="row">> Row count: </label>
  <select name="rowcount" id="rowcount">
    <option value=10>10</option>
    <option value=15>15</option>
    <option value=25>25</option>
    <option value=50>50</option>
  </select>
  <button type="submit" id="rowcountBtn">Uygula</button>
  </div>



  <form class="tabloForm" id="tabloForm">
    <div class="form-group">
      <label for="OGRENCI-AD">Ad: </label>
      <input type="text" id="OGRENCI-AD" name="OGRENCI-AD" minlength="2" maxlength="50" placeholder="John" required>
    </div>

    <div class="form-group">
      <label for="OGRENCI-SOYAD">Soyad: </label>
      <input type="text" id="OGRENCI-SOYAD" name="OGRENCI-SOYAD" minlength="2" maxlength="50" placeholder="Doe" required>
    </div>
  
    <div class="form-group">
      <label for="OGRENCI-NO">Numara: </label>
      <input type="text" id="OGRENCI-NO" name="OGRENCI-NO" min="0" placeholder="150123001" required>
  </div>
  
    <div class="form-group">
      <label for="OGRENCI-BOLUM">Bölüm: </label>
      <input type="text" id="OGRENCI-BOLUM" name="OGRENCI-BOLUM" placeholder="Mühendislik Mühendisliği" required>
    </div>

    <div class="form-group">
      <label for="OGRENCI-YAS">Yaş: </label>
      <input type="text" id="OGRENCI-YAS" name="OGRENCI-YAS" placeholder="20" required>
    </div>
  
    <button type="submit" class="submit-btn" id="ogrenciEkleBtn">Öğrenci Ekle</button>
  </form>

  <div id="fillAllError" style="margin: 10px;"></div>
  <div id="result" style="margin: 10px;"></div>

  <body>
    <div id="app"></div>
  </body>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script></script>
</body>