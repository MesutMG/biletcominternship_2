<template>
  <div class="excel-container">
    <table>
        <thead>
        <tr>
          <th class="excel-corner">/</th>
          <th v-for="(i, index) in ['A','B','C','D','E','F','G','H','I','J']" v-on:click="columnClicked(index+1)" :key="index" class="excel-header">
            {{ i }}
          </th>
        </tr>
      </thead>
      
      <tbody>
        <tr v-for="(i, index) in tableData" :key="index">
          <td class="excel-row-number" v-on:click="rowClicked(index+1)">{{ index + 1 }}</td>

          <td v-for="(j, jndex) in ['C1','C2','C3','C4','C5','C6','C7','C8','C9','C10']" :key="jndex" 
              :class="(selectedColumn === jndex+1 || selectedRow === index+1) ? 'highlighted-cell' : 'cell'">
            
            <input 
                v-if="editingCell.row === jndex && editingCell.col === index"
                v-model="writing"
                v-focus
                @blur="saveCell(index, j)"
                @keyup.enter="saveCell(index, j)"
                class="cell-input"
            />
            
            <div v-else @click="cellClicked(jndex, index)" class="cell-content">
              {{ i[j] ? i[j] : '' }}
            </div>

          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>import axios from 'axios';</script>

<script>
import { setBlockTracking } from 'vue';
    export default {

    directives: {
      focus: {
        mounted(c) {
          c.focus();
        }
      }
    },

    data() {
      return {
        sortButtons: ['id', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10'],
        sortButtonsText: ['id ↓', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10'],
        globalSorting: ['id', 'ASC'],
        rowcount: 10,
        colcount: 10,
        totalpages: 1,
        currentPage: 1,
        response: 2,
        selectedColumn: null,
        selectedRow: null,
        writing: null,
        tableData: [],

        editingCell: { row: null, col: null }
      };
    },

    methods: {
    async createTableHTML(sorting = this.globalSorting, pageNum = this.currentPage) {
      try {
        const queryParams = new URLSearchParams({
          sortparam: sorting[0],
          sortdir: sorting[1],
          pagenum: pageNum
        });

        const response = await axios.get(`/api/table?${queryParams.toString()}`);
        this.response = response.data;
        this.tableData = response.data;
        
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    },

    cellClicked(rowIndex, columnIndex){
          this.writing = '';
          this.editingCell = { row: rowIndex, col: columnIndex };
          this.selectedColumn = null; 
          this.selectedRow = null;
      },

    async saveCell(rowIndex, colKey, pageNum = this.currentPage) {
        if (this.editingCell.row === null) return;
        this.editingCell = { row: null, col: null };
        try {
            const queryParams = new URLSearchParams({
                rowindex: rowIndex,
                column: colKey,
                data: this.writing,
                pagenum: pageNum
            });
            
            await axios.put(`/api/table?${queryParams.toString()}`);
            
            this.tableData[rowIndex][colKey] = this.writing;

        } catch (error) {
            console.error("Error saving cell:", error);
        }
    },

    columnClicked(columnIndex){
        if(this.selectedColumn == columnIndex) { this.selectedColumn = null; }
        else                                  { this.selectedColumn = columnIndex; this.selectedRow = null;}
    },

    rowClicked(rowIndex){
        if(this.selectedRow == rowIndex) { this.selectedRow = null; }
        else                             { this.selectedRow = rowIndex; this.selectedColumn = null;}
    },
  },
  
  mounted() {
    this.createTableHTML();
  }
}
</script>

<style>
html, body {
  margin: 0;
  padding: 0;
  overflow: hidden;
  font-size: 26px;
  cursor: default;
  user-select: none;
}

.excel-container {

  height: 100vh;
  overflow: auto;
  box-sizing: border-box;
}

table {
  width: 200vh;
  border-collapse: collapse;
  table-layout: fixed;
}

th, td {
  border: 1px solid #d4d4d4; 
  padding: 0 3px;
  height: 24px;
}

.excel-corner {
  background-color: #f3f3f3;
  width: 40px;
  position: sticky;
  top: 0;
  left: 0;
  z-index: 3;
  border-bottom: 2px solid #bbb;
  border-right: 2px solid #bbb;
}

.excel-header {
  background-color: #f3f3f3;
  color: #333;
  font-weight: normal;
  text-align: center;
  position: sticky;
  top: 0;
  z-index: 2;
  border-bottom: 2px solid #bbb;
}

.excel-row-number {
  background-color: #f3f3f3;
  color: #666;
  text-align: center;
  font-weight: normal;
  width: 40px;
  position: sticky;
  left: 0;
  z-index: 1;
  border-right: 2px solid #bbb;
}

.cell{
    text-align: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: clip;
    background-color: #ffffff;
}

.cell-content {
  font-size: 20px;
  width: 100%;
  height: 100%;
  min-height: 24px;
}

.cell-input {
  width: 100%;
  height: 100%;
  border: none;
  outline: transparent;
  padding: 0;
  margin: 0;
  font-size: 18px;
  font-family: inherit;
  background-color: white;
  box-sizing: border-box;
}

.cell:hover {
  outline: 1px solid #424242;
}

.highlighted-cell{
    background-color: #bbbbbb;
}

.highlighted-cell:hover {
  outline: 1px solid #424242;
}

</style>
