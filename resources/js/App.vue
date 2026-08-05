<template>
<div @contextmenu.prevent :style="themeStyles">

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
    <div class="menu-item" @click="deleteRow(targetRow)">Delete</div>
    <div class="menu-item" @click="handleAction('Empty')">Empty</div>
  </div>

  <!-- --------------------------- CONTEXT MENU ------------------------------ -->

  <div class="excel-container">
    <table>
        <thead>
        <tr>
          <th class="excel-corner"></th>
          <th v-for="(i, index) in columns" v-on:click="columnClicked(index)" :key="index" class="excel-header" @contextmenu="openContextMenuCol($event,index)">
            {{ i }}
          </th>

          <th v-on:click="addColumn()" class="excel-header add-btn">
            +
          </th>
        </tr>
      </thead>
      
      <tbody>
        <tr v-for="(row_data, row_index) in tableData" :key="row_index">
          <td class="excel-row-number" v-on:click="rowClicked(row_index)" @contextmenu="openContextMenuRow($event, row_index)">{{ row_index + 1 }}</td>

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
        <tr>
            <th v-on:click="addRow()" class="excel-row-number add-btn">
                +
            </th>
        </tr>
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
        Sheet{{ i }}
      </div>
      <div v-on:click="addPage()" class="sheetSelect add-sheet-btn">+</div>
      <div style="flex-grow: 1;"></div>
      <div v-on:click="exitToFiles()" class="sheetSelect exit-btn">Exit</div>
    </div>

  </div>
</div>
</template>

<script setup>
import axios from 'axios';
</script>

<script>
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
    },
    themeStyles() {
      return {
        '--color-primary': this.theme.primary,
        '--color-primary-hover': this.theme.primaryHover,
        '--color-primary-active': this.theme.primaryActive,
        '--color-bg-main': this.theme.bgMain,
        '--color-text-main': this.theme.textMain,
        '--color-border-light': this.theme.borderLight,
        '--color-highlight': '#e3f0e8', // Excel-like light green highlight
      };
    }
  },

  data() {
    return {
      filename: null,
      contextMenuCell: { visible: false, x: 0, y: 0 },
      contextMenuCol: { visible: false, x: 0, y: 0 },
      contextMenuRow: { visible: false, x: 0, y: 0 },
      targetCol: null,
      targetRow: null,
      colcount: 10,
      rowcount: 10,
      totalpages: 1,
      currentPage: 1,
      response: null,
      selectedColumn: null,
      selectedRow: null,
      writing: null,
      tableData: [],
      editingCell: { row: null, col: null },
      
      // Color Palette sync from Files page
      themeColor: 'green',
      theme: {
        primary: '#107c41',
        primaryHover: '#0e6e39',
        primaryActive: '#0c5e30',
        sidebarText: '#ffffff',
        bgMain: '#ffffff',
        textMain: '#333333',
        textSecondary: '#555555',
        borderLight: '#e1e1e1',
        itemBorderHover: '#8bb69a',
      }
    };
  },

  methods: {
    async createTableHTML(pageNum = this.currentPage) {
      try {
        const response = await axios.post('/api/table/get-page', {
          filename: this.filename ,
          pagenum: pageNum
        });

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
      if (this.editingCell.row === null || this.editingCell.col === null) return;
      
      const newValue = this.writing ? this.writing : '';
      this.tableData[rowIndex][colIndex] = newValue;

      this.editingCell = { row: null, col: null };
      this.writing = newValue;

      try {
        await axios.post('/api/table/edit-cell', {
          filename: this.filename,
          pagenum: pageNum,
          rowindex: rowIndex,
          colindex: colIndex,
          data: newValue
        });

      } catch (error) {
        console.error("Error saving cell:", error);
      }
    },

    columnClicked(columnIndex){
        if(this.selectedColumn == columnIndex) { this.selectedColumn = null; }
        else { this.selectedColumn = columnIndex; this.selectedRow = null;}
    },

    rowClicked(rowIndex){
        if(this.selectedRow == rowIndex) { this.selectedRow = null; }
        else { this.selectedRow = rowIndex; this.selectedColumn = null;}
    },

    async addColumn(pageNum = this.currentPage){
        this.colcount++;
        await axios.post('/api/table/add-column', {
          filename: this.filename,
          pagenum: pageNum
        });
    },

    async deleteColumn(colIndex, pageNum = this.currentPage){
      if (colIndex === null || colIndex === undefined) return;

      this.colcount--;
      try {

        await axios.post('/api/table/delete-column', {
          filename: this.filename,
          pagenum: pageNum,
          colindex: colIndex
        });
        
        this.targetCol = null;
        this.closeContextMenuCol();

        await this.createTableHTML();
      } catch (error) {
        console.error("Error deleting column:", error);
      }
    },

    async addRow(pageNum = this.currentPage) {
      try {
        await axios.post('/api/table/add-row', {
          filename: this.filename,
          pagenum: pageNum
        });

        const newRow = {};
        for (let i = 1; i <= this.colcount; i++) {
          newRow[i] = '';
        }

        this.tableData.push(newRow);
        
        this.rowcount++;
        
      } catch (error) {
        console.error("Error adding row:", error);
      }
    },

    async deleteRow(rowIndex, pageNum = this.currentPage){
      if (rowIndex === null || rowIndex === undefined) return;

      this.rowcount--;
      try {
        await axios.post('/api/table/delete-row', {
          filename: this.filename,
          rowindex: rowIndex,
          pagenum: pageNum
        });

        this.targetRow = null;
        this.closeContextMenuCol();

        await this.createTableHTML();
      } catch (error) {
        console.error("Error deleting column:", error);
      }
    },

    async addPage(){
      this.totalpages++;
      await axios.post('/api/table/add-page', { filename: this.filename, pagenum: this.totalpages });
      this.currentPage = this.totalpages;
      this.createTableHTML();
    },

    async changePageTo(newPageNum){
      this.currentPage = newPageNum;
      this.createTableHTML();
    },

    async exitToFiles(){
      window.location.href = '/files';
    },

    range(start, end, step = 1) {
      let result = [];
      for (let a = start; a < end; a += step) {
          result.push(a);
      }
    return result;
    },

    /* ------------------------------------ CONTEXT MENU ------------------------------------ */ 
    openContextMenuCell(event) {
      this.closeContextMenuCol();
      this.closeContextMenuRow();
      this.contextMenuCell.x = event.clientX;
      this.contextMenuCell.y = event.clientY;
      this.contextMenuCell.visible = true;
    },
    closeContextMenuCell() { this.contextMenuCell.visible = false; },

    openContextMenuCol(event, index) {
      this.closeContextMenuCell();
      this.closeContextMenuRow();
      this.targetCol = index;
      this.contextMenuCol.x = event.clientX;
      this.contextMenuCol.y = event.clientY;
      this.contextMenuCol.visible = true;
    },
    closeContextMenuCol() { this.contextMenuCol.visible = false; },

    openContextMenuRow(event, index) {
      this.closeContextMenuCell();
      this.closeContextMenuCol();
      this.targetRow = index;
      this.contextMenuRow.x = event.clientX;
      this.contextMenuRow.y = event.clientY;
      this.contextMenuRow.visible = true;
    },
    closeContextMenuRow() { this.contextMenuRow.visible = false; },

    handleAction(action) {
      console.log(`Action clicked: ${action}`);
      this.closeContextMenuCell();
      this.closeContextMenuCol();
    },
  },
  
  async mounted() {
    this.filename = localStorage.getItem('activeFileName');
    const cachedData = localStorage.getItem('activeSheetData');
  
    if (cachedData) {
      const parsed = JSON.parse(cachedData);
      this.tableData = parsed.data;
      this.rowcount = parsed.rowcount;
      this.colcount = parsed.colcount;
      this.totalpages = parsed.pagecount;
    } else if (this.filename) {
      this.createTableHTML();
    }

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
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 14px;
  cursor: default;
  user-select: none;
  background-color: var(--color-bg-main);
}

.excel-container {
  height: calc(100vh - 35px);
  overflow: auto;
  box-sizing: border-box;
}

table {
  border-collapse: collapse;
  table-layout: fixed;
  background-color: #fff;
}

th, td {
  width: 80px;
  min-width: 80px;
  max-width: 80px;
  overflow: hidden;
  border: 1px solid var(--color-border-light); 
  padding: 0 4px;
  height: 22px;
}


.excel-corner, .excel-header, .excel-row-number {
  background-color: #e6e6e6;
  color: #333;
  font-weight: normal;
  text-align: center;
  border: 1px solid #c8c8c8;
}

.excel-corner {
  width: 35px;
  min-width: 35px;
  position: sticky;
  top: 0;
  left: 0;
  z-index: 3;
}

.excel-header {
  position: sticky;
  top: 0;
  z-index: 2;
  cursor: pointer;
}

.excel-row-number {
  width: 35px;
  min-width: 35px;
  position: sticky;
  left: 0;
  z-index: 1;
  cursor: pointer;
}

.excel-header:hover, .excel-row-number:hover {
  background-color: #d4d4d4;
}

.add-btn {
  color: #666;
  font-weight: bold;
}


.cell {
  background-color: #ffffff;
  white-space: nowrap;
  text-align: left;
}

.cell-content {
  font-size: 14px;
  line-height: 22px;
}

.cell-input {
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  border: none;
  outline: none;
  padding: 0;
  margin: 0;
  font-size: 14px;
  font-family: inherit;
  background-color: white;
}


.active-cell {
  outline: 2px solid var(--color-primary);
  outline-offset: -2px;
  position: relative;
  z-index: 9;
}


.active-cell::after {
  content: '';
  position: absolute;
  bottom: -3px;
  right: -3px;
  width: 6px;
  height: 6px;
  background-color: var(--color-primary);
  border: 1px solid #fff;
  z-index: 10;
  cursor: crosshair;
}

.highlighted-cell {
  background-color: var(--color-highlight);
}


.sheetContainer {
  position: fixed;
  bottom: 0;
  padding-inline: 20px;
  width: 100%;
  height: 35px;
  background-color: #f3f3f3;
  border-top: 1px solid #c8c8c8;
  display: flex;
  align-items: center;
  z-index: 100;
  box-sizing: border-box;
}

.sheetSelect {
  box-sizing: border-box;
  padding: 0 16px;
  height: 100%;
  display: flex;
  align-items: center;
  font-size: 13px;
  color: #333;
  cursor: pointer;
  border-right: 1px solid #d4d4d4;
  border-bottom: 3px solid transparent;
  background-color: #f3f3f3;
}

.active-sheet {
  background-color: #ffffff;        
  color: var(--color-primary);
  font-weight: 600;
  border-bottom-color: var(--color-primary);
}

.sheetSelect:hover {
  background-color: #eaeaea;
}

.add-sheet-btn {
  font-size: 18px;
  font-weight: bold;
  padding: 0 12px;
}

.exit-btn {
  border-inline: 1px solid #d4d4d4;
}

.exit-btn:hover {
  background-color: var(--color-delete-hover-bg, #fde8e8);
  color: var(--color-delete-text, #a80000);
}

.custom-context-menu {
  position: fixed;
  z-index: 1000;
  background-color: #ffffff;
  border: 1px solid #c8c8c8;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
  padding: 4px 0;
  min-width: 160px;
  font-size: 13px;
}

.menu-item {
  padding: 6px 20px;
  cursor: pointer;
  color: #333;
}

.menu-item:hover {
  background-color: #f3f3f3;
}
</style>