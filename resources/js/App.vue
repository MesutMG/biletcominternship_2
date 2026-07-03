<style>
*{
padding-block: 3px;
box-sizing: border-box;
user-select: none;
}

body{
margin-inline: 25%;
min-width: 600px;
}

table{
border-collapse: collapse;
width: 100%;
table-layout: fixed;
}

th{
color: #ffffff;
background-color: #54585d;
border: 3px solid #54585d;
padding: 8px;
}

.sortBtn{
    cursor: default;
    transition:ease 0.3s;
}
.sortBtn:hover{
    background-color: #64686e;
    border-color: transparent;
}
.sortBtn:active{
    background-color: #a3a3a3;
    border-style: groove;
    border-color: #54585d;
}

.TABLOID{
  margin: 0;
  padding: 0;
}

#insideTable{
    padding-block: 0px;
}

.tabloForm{
    display:grid;
    justify-content: center;
}
.form-group{
    display: grid;
    padding-bottom: 5px;
}

/*------- FORM EDITING PART -------*/
#insideTable form {
    margin: 0;
    padding: 0;
    display: block;
}
#insideTable form input {
    width: 100%;
    box-sizing: border-box;
    margin: 0;
    padding: 4px; 
    border: 1px solid #ccc;
    border-radius: 2px;
    font-family: inherit;
    font-size: inherit;
    background-color: #fff;
    outline: none;
}
#insideTable form input:focus {
    border-color: #54585d;
}
    /*------- FORM EDITING PART -------*/

    /*------- PAGINATION PART ------ */
.pagination{
    margin-block: 10px;
    text-align: center;
}
.pagination a {
    color: black;
    padding: 8px 16px;
    text-decoration: none;
    transition: background-color .3s;
    display: inline-block;
}
.pagination a.pg_active {
    background-color: dodgerblue;
    color: white;
}
.pagination a:hover:not(.pg_active) {background-color: #ddd;}
/*------- PAGINATION PART ------ */

td{
    border: 0.5px solid #54585d4e;
    padding: 4px;
}
.rowedit{
    text-align: center;
    color: #ffffff;
    background-color: #54585d;
    padding: 8px;
    cursor: default;
    transition:ease 0.3s;
}
.rowedit:hover{
    background-color: #64686e;
}
.rowedit:active{
    background-color: #a3a3a3;
}
.filter{
    width: 100%;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 4px;
    outline: none;
}
.filter:focus{
    border-color: #54585d;
}
    
tr{
    background-color: #ffffff;
}
tr.highlight{
    background-color: #f9fafb;
}
</style>

<template>
  <table>
      <tr id="sorting-table">
          <td style="border: 0;"></td>
          <th v-on:click="sortChange(0)" class="sortBtn">{{sortButtonsText[0]}}</th>
          <th v-on:click="sortChange(1)" class="sortBtn">{{sortButtonsText[1]}}</th>
          <th v-on:click="sortChange(2)" class="sortBtn">{{sortButtonsText[2]}}</th>
          <th v-on:click="sortChange(3)" class="sortBtn">{{sortButtonsText[3]}}</th>
          <th v-on:click="sortChange(4)" class="sortBtn">{{sortButtonsText[4]}}</th>
          <th v-on:click="sortChange(5)" class="sortBtn">{{sortButtonsText[5]}}</th>
          <td style="border: 0;"></td>
      </tr>
      <tr>
          <td style="border: 0;"></td>
          <td><input type="text" placeholder="Ara: ID" v-model="globalFiltering[0]" class="filter"></td>
          <td><input type="text" placeholder="Ara: AD" v-model="globalFiltering[1]" class="filter"></td>
          <td><input type="text" placeholder="Ara: SOYAD" v-model="globalFiltering[2]" class="filter"></td>
          <td><input type="text" placeholder="Ara: NO" v-model="globalFiltering[3]" class="filter"></td>
          <td><input type="text" placeholder="Ara: BOLUM" v-model="globalFiltering[4]" class="filter"></td>
          <td><input type="text" placeholder="Ara: YAS" v-model="globalFiltering[5]" class="filter"></td>
          <td style="border: 0;"><button v-on:click="loadTable()" class="filter">Search</button></td>
      </tr>
  </table>

  <div id="TABLOID" class="TABLOID">
    <table v-if="studentsData && studentsData.length">
      <tr v-for="(student, index) in studentsData" :key="student.NO">
        <template v-if="currentlyEditing == student.NO">
          <td v-on:click="!editSaveIsDisabled && saveEdit(student.NO)" class="rowedit" :style="editSaveIsDisabled ? 'cursor: not-allowed;' : 'cursor: pointer;'">Kaydet</td>
          <td>{{student.ID}}</td>
          <td><input type="text" v-model="editForm.editName" class="filter"></td>
          <td><input type="text" v-model="editForm.editLastName" class="filter"></td>
          <td>{{student.NO}}</td> <td><input type="text" v-model="editForm.editMaj" class="filter"></td>
          <td><input type="text" v-model="editForm.editAge" class="filter" style="width: 50px;"></td>
          <td v-on:click="cancelEdit(index)" class="rowedit">Vazgeç</td>
        </template>

        <template v-else>
          <td v-on:click="editClicked(student, index)" class="rowedit">Düzenle</td>
          <td>{{student.ID}}</td>
          <td>{{student.AD}}</td>
          <td>{{student.SOYAD}}</td>
          <td>{{student.NO}}</td>
          <td>{{student.BOLUM}}</td>
          <td>{{student.YAS}}</td>
          <td v-on:click="ogrenciSil(student.NO, index)" class="rowedit">Sil</td>
        </template>
      </tr>
    </table>

  </div>

  <div id="pagination" class="pagination">
    <a v-on:click="changePageTo(1)" href="#"><<</a>
    <a v-on:click="changePageTo(Math.max(currentPage - 1, 1))" href="#"><</a>

    <template v-for="index in totalpages">
      <a v-if="currentPage == index" v-on:click="changePageTo(index)" href="#" class="pg_active">{{ index }}</a>
      <a v-else href="#" class="page-link" v-on:click="changePageTo(index)">{{ index }}</a>
    </template>

    <a v-on:click="changePageTo(Math.min(currentPage + 1, totalpages))" href="#">></a>
    <a v-on:click="changePageTo(totalpages)" href="#">>></a>
  </div>

  <div>
  <label for="row">Row count: </label>
  <select v-model="rowcount">
    <option v-on:click="applyRowCount(10)">10</option>
    <option v-on:click="applyRowCount(15)">15</option>
    <option v-on:click="applyRowCount(25)">25</option>
  </select>
  </div>



  <form class="tabloForm">
    <div class="form-group">
      <label>Ad: </label>
      <input type="text" v-model="ogrenciEklemeParametreleri[0]" minlength="2" maxlength="50" placeholder="John" required>
    </div>

    <div class="form-group">
      <label>Soyad: </label>
      <input type="text" v-model="ogrenciEklemeParametreleri[1]" minlength="2" maxlength="50" placeholder="Doe" required>
    </div>

    <div class="form-group">
      <label>Numara: </label>
      <input type="text" v-model="ogrenciEklemeParametreleri[2]" min="0" placeholder="150123001" required>
    </div>
    
    <div>
      <label for="OGRENCI-BOLUM">Bölüm: </label>
      <input type="text" v-model="ogrenciEklemeParametreleri[3]" placeholder="Mühendislik Mühendisliği" required>
    </div>

    <div>
      <label for="OGRENCI-YAS">Yaş: </label>
      <input type="text" v-model="ogrenciEklemeParametreleri[4]" placeholder="20" required>
    </div>

    <div class="form-group">
      <button v-on:click="ogrenciEkle()" :disabled="ogrenciEkleIsDisabled" :style="ogrenciEkleIsDisabled ? 'cursor: not-allowed;' : 'cursor: pointer;'">Öğrenci Ekle</button>
    </div>
  </form>

  <h2 class="warning">{{ uyariKutusu }}</h2>

</template>


<script setup>
import { list } from '@primeuix/themes/aura/autocomplete';
import axios from 'axios';
</script>

<script>
  export default {
    data() {
      return {
        sortButtons: ['ID', 'AD', 'SOYAD', 'NO', 'BOLUM', 'YAS'],
        sortButtonsText: ['ID ↓', 'AD', 'SOYAD', 'NO', 'BOLUM', 'YAS'],
        globalSorting: ['ID', 'ASC'],
        globalFiltering: ['', '', '', '', '', ''],
        rowcount: 10,
        currentlyEditing: null,
        totalpages: 1,
        currentPage: 1,
        currentTable: [],
        tempEditing: [],
        
        editingId: null,
        editForm: {
          editName: '',
          editLastName: '',
          editMaj: '',
          editAge: ''
        },
        ogrenciEklemeParametreleri: ['', '', '', '', ''],
        studentsData: null,
        uyariKutusu: null
      };
    },

    mounted() {
      this.loadTable();
    },

    methods: {
      async createTableHTML(data){
        this.studentsData = data;
      },

      checkFilled(){
        for (let i = 0; i<5; i++) {
          if(this.ogrenciEklemeParametreleri[i].length > 2 && this.ogrenciEklemeParametreleri[i].length < 51){
            return true;
          }
        }
      },

      async loadTable(sorting = this.globalSorting, filters = this.globalFiltering, requestedcount = this.rowcount, pg = this.currentPage){
        const queryParams = new URLSearchParams({
            sortparam: sorting[0],
            sortdir: sorting[1],
            requestedcount: requestedcount,
            page: pg,
            filterId: filters[0],
            filterName: filters[1],
            filterLastName: filters[2],
            filterNum: filters[3],
            filterMaj: filters[4],
            filterAge: filters[5]
        });

        const response = (await axios.get(`/api/students?${queryParams.toString()}`)).data;
        this.totalpages = Math.ceil(response.total / response.per_page);
        this.createTableHTML(response.data);
      },

      async ogrenciSil(deleteNum) {
        await axios.delete(`/api/students/${deleteNum}`);
        this.loadTable();
      },
      
      async ogrenciEkle(){
        
        try {
          const payload = {
              studentName: this.ogrenciEklemeParametreleri[0],
              studentLastName: this.ogrenciEklemeParametreleri[1],
              studentNum: this.ogrenciEklemeParametreleri[2],
              studentMajor: this.ogrenciEklemeParametreleri[3],
              studentAge: this.ogrenciEklemeParametreleri[4]
          };

          const response = await axios.post('/api/students', payload);
          
          if (response.data.status === 'success') {
              this.ogrenciEklemeParametreleri = ['', '', '', '', ''];
              this.uyariKutusu = "";
              this.loadTable();
          }
          else throw error;
        } catch (error) {
            console.error("Ekleme işlemi sırasında hata oluştu:", error.response?.data || error.message);
            this.uyariKutusu = "Ekleme işlemi sırasında hata oluştu";
        }
      },
      
      async applyRowCount(c){
        this.rowcount = c;
        this.currentPage = 1;
        this.loadTable();
      },

      async changePageTo(c){
        this.currentPage = c;
        this.loadTable();
      },

      async sortChange(c){
        if(this.globalSorting[0] === this.sortButtons[c]){
          if(this.globalSorting[1] === 'DESC'){
                this.globalSorting[1] = 'ASC';
                this.sortButtonsText[c] = this.sortButtons[c] + ' ↓';
                this.loadTable();
            } else {
                this.globalSorting[1] = 'DESC';
                this.sortButtonsText[c] = this.sortButtons[c] + ' ↑';
                this.loadTable();
            }
        } else {
            this.sortButtonsText = ['ID', 'AD', 'SOYAD', 'NO', 'BOLUM', 'YAS'];
            this.globalSorting = [this.sortButtons[c] ,'ASC'];
            this.sortButtonsText[c] = this.sortButtons[c] + ' ↓';
            this.loadTable();
        }
      },

      editClicked(student, index){
        this.currentlyEditing = student.NO;
        this.editForm = {
          editName: student.AD,
          editLastName: student.SOYAD,
          editMaj: student.BOLUM,
          editAge: student.YAS
        }
      },

      async saveEdit(studentNo, index){
        await axios.put(`/api/students/${studentNo}`, this.editForm);
        this.currentlyEditing = null;
        this.loadTable();
      },

      cancelEdit(index) {
        this.currentlyEditing = null;
      },
    },

    computed: {
      ogrenciEkleIsDisabled() {
        const name = this.ogrenciEklemeParametreleri[0]?.trim() || '';
        const lastName = this.ogrenciEklemeParametreleri[1]?.trim() || '';
        const num = this.ogrenciEklemeParametreleri[2]?.trim() || '';
        const major = this.ogrenciEklemeParametreleri[3]?.trim() || '';
        const age = this.ogrenciEklemeParametreleri[4]?.trim() || '';

        if (name.length < 2 || lastName.length < 2 || num.length < 1 || major.length < 2 || age.length < 1) {
          return true;
        }

        const alphaRegex = /[^a-zçğıöşüÇĞİÖŞÜ ']/i;
        if (alphaRegex.test(name) || alphaRegex.test(lastName) || alphaRegex.test(major)) {
          return true;
        }

        const numericRegex = /[^0-9]/;
        if (numericRegex.test(num) || numericRegex.test(age)) {
          return true;
        }

        return false;
      },

    editSaveIsDisabled() {
        const name = this.editForm.editName?.trim() || '';
        const lastName = this.editForm.editLastName?.trim() || '';
        const major = this.editForm.editMaj?.trim() || '';
        const age = this.editForm.editAge?.toString().trim() || ''; // Convert to string just in case it's a number type

        // Minimum length checks
        if (name.length < 2 || lastName.length < 2 || major.length < 2 || age.length < 1) {
          return true;
        }

        // Alpha checks for Text fields
        const alphaRegex = /[^a-zçğıöşüÇĞİÖŞÜ ']/i;
        if (alphaRegex.test(name) || alphaRegex.test(lastName) || alphaRegex.test(major)) {
          return true;
        }

        // Numeric check for Age
        const numericRegex = /[^0-9]/;
        if (numericRegex.test(age)) {
          return true;
        }

        return false;
      }
    }
  };
</script>
