<template>
<div @contextmenu.prevent>

  <!-- --------------------------- CONTEXT MENU ------------------------------ -->
  <div 
    v-if="contextMenuCell.visible"
    class="custom-context-menu"
    :style="{ top: contextMenuCell.y + 'px', left: contextMenuCell.x + 'px' }"
  >
    <div class="menu-item" @click="handleAction('Copy')">Copy</div>
    <div class="menu-item" @click="handleAction('Cut')">Cut</div>
    <div class="menu-item" @click="handleAction('Paste')">Paste</div>
    <div class="menu-item" @click="handleAction('Mark')">Mark</div>
    <div class="menu-item" @click="handleAction('Delete')">Delete</div>
  </div>

  <div 
    v-if="contextMenuCol.visible"
    class="custom-context-menu"
    :style="{ top: contextMenuCol.y + 'px', left: contextMenuCol.x + 'px' }"
  >
    <div class="menu-item" @click="deleteColumn(targetCol)">Delete</div>
    <div class="menu-item" @click="handleAction('Empty')">Empty</div>
  </div>

  <div 
    v-if="contextMenuRow.visible"
    class="custom-context-menu"
    :style="{ top: contextMenuRow.y + 'px', left: contextMenuRow.x + 'px' }"
  >
    <div class="menu-item" @click="handleAction('Empty')">Empty</div>
  </div>

  <!-- --------------------------- CONTEXT MENU ------------------------------ -->

  <div class="excel-container">
    <table>
        <thead>
        <tr>
          <th class="excel-corner">/</th>
          <th v-for="(i, index) in columns" v-on:click="columnClicked(index)" :key="index" class="excel-header" @contextmenu="openContextMenuCol($event,index)">
            {{ i }}
          </th>

          <th v-on:click="addColumn()" class="excel-header">
            +
          </th>
        </tr>
      </thead>
      
      <tbody>
        <tr v-for="(row_data, row_index) in tableData" :key="row_index">
          <td class="excel-row-number" v-on:click="rowClicked(row_index)" @contextmenu="openContextMenuRow">{{ row_index + 1 }}</td>

          <td v-for="(n, col_index) in colcount" :key="col_index" 
            :class="[
              (selectedColumn === col_index || selectedRow === row_index) ? 'highlighted-cell' : 'cell',
              { 'active-cell': editingCell.row === col_index && editingCell.col === row_index }
            ]"
            @click="cellClicked(col_index, row_index)"
            @contextmenu="openContextMenuCell"
          >
            
            <input 
                v-if="editingCell.row === col_index && editingCell.col === row_index"
                v-model="writing"
                v-focus
                @blur="saveCell(row_index, col_index)"
                @keyup.enter="saveCell(row_index, col_index)"
                class="cell-input"
            />
            
            <div v-else class="cell-content">
              {{ row_data[col_index] ? row_data[col_index] : '' }}
            </div>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <th v-on:click="addRow()" class="excel-row-number">
            +
        </th>
      </tfoot>
    </table>

    <div class="sheetContainer">
      <div 
        v-for="i in totalpages" 
        v-on:click="changePageTo(i)" 
        :key="i" 
        class="sheetSelect"
        :class="{ 'active-sheet': i === currentPage }"
      >
        Page{{ i }}
      </div>
      <div v-on:click="addPage()" class="sheetSelect" style="margin-right: 20px;">+</div>
      <div v-on:click="saveToJSON()" class="sheetSelect">Indir</div>
    </div>

  </div>
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

    computed: {
      columns() {
        const cols = [];
        for (let i = 0; i < this.colcount; i++) {
          let letter = '';
          let temp = i;
          while (temp >= 0) {
            letter = String.fromCharCode((temp % 26) + 65) + letter;
            temp = Math.floor(temp / 26) - 1;
          }
          cols.push(letter);
        }
        return cols;
      }
    },

    data() {
      return {
        contextMenuCell: {
          visible: false,
          x: 0,
          y: 0
        },
        contextMenuCol: {
          visible: false,
          x: 0,
          y: 0
        },
        contextMenuRow: {
          visible: false,
          x: 0,
          y: 0
        },
        targetCol: null,
        sortButtons: ['id', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10'],
        sortButtonsText: ['id ↓', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8', 'C9', 'C10'],
        globalSorting: ['id', 'ASC'],
        colcount: 10,
        rowcount: 10,
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
        
        this.tableData = this.response.data;
        this.rowcount = this.response.rowcount;
        this.colcount = this.response.colcount;
        this.totalpages = this.response.pagecount;
        
      } catch (error) {
        console.error("Error fetching data:", error);
      }
    },

    cellClicked(colIndex, rowIndex) {
      this.writing = this.tableData[rowIndex][colIndex];
      this.editingCell = { row: colIndex, col: rowIndex };
      this.selectedColumn = null; 
      this.selectedRow = null;
    },

    async saveCell(rowIndex, colIndex, pageNum = this.currentPage) {
      if (this.editingCell.row === null || this.editingCell.col === null) { return };
      
      console.log(rowIndex, colIndex, this.tableData[rowIndex][colIndex], this.writing, pageNum);

      const newValue = this.writing ? this.writing : '';
      this.tableData[rowIndex][colIndex] = newValue;

      this.editingCell = { row: null, col: null };
      this.writing = this.writing ? this.writing : '';

      this.editingCell = { row: null, col: null };

      try {
        const queryParams = new URLSearchParams({
          rowindex: rowIndex,
          colindex: colIndex,
          data: this.writing,
          pagenum: pageNum,
        });
        
        await axios.put(`/api/table?${queryParams.toString()}`);

      } catch (error) {
        console.error("Error saving cell:", error);
      }
    },

    columnClicked(columnIndex){
        if(this.selectedColumn == columnIndex) { this.selectedColumn = null; }
        else                                   { this.selectedColumn = columnIndex; this.selectedRow = null;}
    },

    rowClicked(rowIndex){
        if(this.selectedRow == rowIndex) { this.selectedRow = null; }
        else                             { this.selectedRow = rowIndex; this.selectedColumn = null;}
    },

    async addColumn(pageNum = this.currentPage){
        this.colcount++;
        await axios.post('/api/table/add-column', { pagenum: pageNum });
    },

    async deleteColumn(colIndex, pageNum = this.currentPage){
      if (colIndex === null || colIndex === undefined) return;

      this.colcount--;
      try {
        
        await axios.post('/api/table/delete-column', { colindex: colIndex, pagenum: pageNum });
        
        this.targetColIndex = null;
        this.closeContextMenuCol();
        
        await this.createTableHTML();
      } catch (error) {
        console.error("Error deleting column:", error);
      }
    },

    async addRow(pageNum = this.currentPage) {
      try {
        await axios.post('/api/table/add-row', { pagenum: pageNum });

        const newRow = {};
        for (let i = 1; i <= this.colcount; i++) {
          newRow['C' + i] = '';
        }

        this.tableData.push(newRow);

        this.rowcount++;

      } catch (error) {
        console.error("Error adding row:", error);
      }
    },

    async addPage(){
      this.totalpages++;
      await axios.post('/api/table/add-page', { pagenum: this.totalpages });
      this.currentPage = this.totalpages;
      this.createTableHTML();
    },

    async changePageTo(newPageNum){
      this.currentPage = newPageNum;
      this.createTableHTML();
    },

    range(start, end, step = 1) {
      let result = [];
      for (let a = start; a < end; a += step) {
          result.push(a);
      }
    return result;
    },

    saveToJSON() {
      const jsonString = JSON.stringify(this.tableData, null, 2);
      
      const blob = new Blob([jsonString], { type: 'application/json' });
      const link = document.createElement('a');
      
      link.href = URL.createObjectURL(blob);
      link.download = `sheet_${this.currentPage}_data.json`;
      link.click();
      
      //clean up memory URL
      URL.revokeObjectURL(link.href);
    },

    /* ------------------------------------ CONTEXT MENU ------------------------------------ */ 
    openContextMenuCell(event) {
      this.closeContextMenuCol();
      this.closeContextMenuRow();
      this.contextMenuCell.x = event.clientX;
      this.contextMenuCell.y = event.clientY;
      this.contextMenuCell.visible = true;
    },

    closeContextMenuCell() {
      this.contextMenuCell.visible = false;
    },

    openContextMenuCol(event, index) {
      this.closeContextMenuCell();
      this.closeContextMenuRow();
      this.targetCol = index;
      this.contextMenuCol.x = event.clientX;
      this.contextMenuCol.y = event.clientY;
      this.contextMenuCol.visible = true;
    },

    closeContextMenuCol() {
      this.contextMenuCol.visible = false;
    },

    openContextMenuRow(event) {
      this.closeContextMenuCell();
      this.closeContextMenuCol();
      this.contextMenuRow.x = event.clientX;
      this.contextMenuRow.y = event.clientY;
      this.contextMenuRow.visible = true;
    },

    closeContextMenuRow() {
      this.contextMenuRow.visible = false;
    },

    handleAction(action) { /* ------------------------------- handle methods ---------------------------------*/
      console.log(`Action clicked: ${action}`);
      this.closeContextMenuCell();
      this.closeContextMenuCol();
    },
    /* ------------------------------------ CONTEXT MENU ------------------------------------ */
  },
  
  mounted() {
    this.createTableHTML();
    window.addEventListener('click', this.closeContextMenuCell);
    window.addEventListener('click', this.closeContextMenuCol);
    window.addEventListener('click', this.closeContextMenuRow);
  },

  unmounted() {
    window.removeEventListener('click', this.closeContextMenuCell);
    window.removeEventListener('click', this.closeContextMenuCol);
    window.removeEventListener('click', this.closeContextMenuRow);
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
  padding-bottom: 40px;
}

table {
  border-collapse: collapse;
  table-layout: fixed;
}

th, td {
  width: 100px;
  max-width: 100px;
  overflow:hidden;
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
  width: 100px;
  min-width: 100px;
  max-width: 100px;
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
  min-width: 100px;
  text-align: left;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: clip;
  background-color: #ffffff;
}

.cell-content {
  font-size: 20px;
  min-height: 24px;
}

.cell-input {
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  border: none;
  outline: transparent;
  padding: 0;
  margin: 0;
  font-size: 18px;
  font-family: inherit;
  background-color: white;
}

.cell:hover {
  outline: 1px solid #a6a6a6;
}

.active-cell {
  outline: 2px solid #424242;
  outline-offset: -2px;
  position: relative;
  z-index: 9;
}

.active-cell:hover {
  outline: 2px solid #424242;
  outline-offset: -2px;
  position: relative;
  z-index: 9;
}

.highlighted-cell{
    background-color: #bbbbbb;
}

.highlighted-cell:hover {
  outline: 1px solid #424242;
}

.sheetContainer {
  position: fixed;
  bottom: 10px;
  right: 10px;
  z-index: 9;
  display: flex;
  flex-direction: row;
  gap: 1px;
  transition: 0.3s ease;
}

.sheetSelect {
  background-color: #f3f3f3;
  border: 1px solid #292929;
  padding: 5px;
  font-size: 36px;
  font-style: italic;
  cursor: pointer;
}

.active-sheet {
  background-color: #f7deb1;        
  font-weight: bold;
}

.custom-context-menu {
  position: fixed;
  z-index: 1000;
  background-color: #ffffff;
  border: 1px solid #ccc;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  border-radius: 4px;
  padding: 4px 0;
  min-width: 140px;
}

.menu-item {
  padding: 8px 16px;
  font-size: 16px;
  cursor: pointer;
  color: #333;
}

.menu-item:hover {
  background-color: #f0f0f0;
}

</style>
