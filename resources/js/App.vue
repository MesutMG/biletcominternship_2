<template>
<div @contextmenu.prevent :style="themeStyles">

  <!-- --------------------------- CONTEXT MENU ------------------------------ -->
  <div 
    v-if="contextMenuCell.visible"
    class="custom-context-menu"
    :style="{ top: contextMenuCell.y + 'px', left: contextMenuCell.x + 'px' }"
  >
    <div class="menu-header">{{ columns[selectedCell.col] }}{{ selectedCell.row + 1}}</div>
    <div class="menu-divider"></div>

    <div class="menu-item" @click="copyCell()">Copy</div>
    <div class="menu-item" @click="cutCell()">Cut</div>
    <div class="menu-item" @click="pasteCell()">Paste</div>
    <div class="menu-item" @click="clearCellData(selectedCell.row, selectedCell.col)">Delete</div>
  </div>

  <div 
    v-if="contextMenuCol.visible"
    class="custom-context-menu"
    :style="{ top: contextMenuCol.y + 'px', left: contextMenuCol.x + 'px' }"
  >
    <div class="menu-header">{{ columns[targetCol] }}</div>
    <div class="menu-divider"></div>

    <div class="menu-item" @click="deleteColumn(targetCol)">Delete</div>
    <div class="menu-item" @click="fillColumnWith(targetCol, '')">Empty</div>
    <div class="menu-item" @click="openColFillPrompt(targetCol)">Fill</div>
    <div class="menu-item" v-on:mouseover="openInsertSubmenuCol()">Insert</div>
  </div>

  <div
    v-if="insertSubmenuCol.visible"
    class="custom-context-menu"
    :style="{ top: insertSubmenuCol.y + 'px', left: insertSubmenuCol.x + 'px' }"
    v-on:mouseleave="closeInsertSubmenuCol()"
  >
    <div class="menu-item" @click="insertColumn(targetCol+1)">Insert Right</div>
    <div class="menu-item" @click="insertColumn(targetCol)">Insert Left</div>
  </div>
  
  <div 
    v-if="contextMenuRow.visible"
    class="custom-context-menu"
    :style="{ top: contextMenuRow.y + 'px', left: contextMenuRow.x + 'px' }"
  >
    <div class="menu-header">{{ targetRow + 1 }}</div>
    <div class="menu-divider"></div>

    <div class="menu-item" @click="deleteRow(targetRow)">Delete</div>
    <div class="menu-item" @click="fillRowWith(targetRow, '')">Empty</div>
    <div class="menu-item" @click="openRowFillPrompt(targetRow)">Fill</div>
    <div class="menu-item" v-on:mouseover="openInsertSubmenuRow()">Insert</div>
  </div>

  <div
    v-if="insertSubmenuRow.visible"
    class="custom-context-menu"
    :style="{ top: insertSubmenuRow.y + 'px', left: insertSubmenuRow.x + 'px' }"
    v-on:mouseleave="closeInsertSubmenuRow()"
  >
    <div class="menu-item" @click="insertRow(targetRow)">Insert Above</div>
    <div class="menu-item" @click="insertRow(targetRow+1)">Insert Below</div>
  </div>

  <!-- --------------------------- CONTEXT MENU ------------------------------ -->


  <!-- --------------------------- PROMPT OVERLAY ------------------------------ -->
  <div v-if="promptScene === 1" class="modal-overlay" @click.self="promptScene = 0">
    <div class="modal-box">
      <p>Enter text to fill data:</p>
      <input v-model="promptData" class="file-name-input" v-focus @keyup.enter="submitFillPrompt" />
      <div style="margin-top: 12px; display: flex; gap: 8px; justify-content: center;">
        <button class="modal-close-btn" @click="submitFillPrompt">Submit</button>
        <button class="modal-close-btn" style="background-color: #777;" @click="promptScene = 0">Cancel</button>
      </div>
    </div>
  </div>
  <!-- --------------------------- PROMPT OVERLAY ------------------------------ -->


  <div class="excel-container">
    <table>
        <thead>
          <tr>
            <th class="excel-corner"></th>
            <th v-for="(i, index) in columns" :key="index" 
                :class="['excel-header', { 'header-highlighted': selectedColumn === index || selectedCell.col === index }]"
                @click="columnClicked(index)" 
                @contextmenu="openContextMenuCol($event, index); columnClicked(index);">
              {{ i }}
            </th>
            <th @click="addColumn()" class="excel-header add-btn">
              +
            </th>
        </tr>
      </thead>
      
      <tbody>
        <tr v-for="(row_data, row_index) in tableData" :key="row_index">
          <td :class="['excel-row-number', { 'header-highlighted': selectedRow === row_index || selectedCell.row === row_index }]" 
              @click="rowClicked(row_index)" 
              @contextmenu="openContextMenuRow($event, row_index); rowClicked(row_index);">
            {{ row_index + 1 }}
          </td>

          <td v-for="(n, col_index) in colcount" :key="col_index" 
            :class="[
              (selectedColumn === col_index || selectedRow === row_index) ? 'highlighted-cell' : 'cell',
              { 
                'selected-cell': selectedCell.col === col_index && selectedCell.row === row_index,
                'active-cell': editingCell.col === col_index && editingCell.row === row_index 
              }
            ]"
            @click="selectCell(col_index, row_index)"
            @dblclick="editCell(col_index, row_index)"
            @contextmenu="openContextMenuCell($event, col_index, row_index); selectCell(col_index, row_index);"
          >
            <input 
              v-if="editingCell.col === col_index && editingCell.row === row_index"
              v-model="writing"
              v-focus
              @blur="saveCell(row_index, col_index)"
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
        '--color-highlight': '#e3f0e8',
      };
    }
  },

  data() {
    return {
      filename: null,
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
      selectedCell: { row: null, col: null },
      editingCell: { row: null, col: null },
      contextMenuCellRowIndex: null,
      contextMenuCellColIndex: null,
      contextMenuCell: { visible: false, x: 0, y: 0 },
      contextMenuCol: { visible: false, x: 0, y: 0 },
      contextMenuRow: { visible: false, x: 0, y: 0 },
      insertSubmenuCol: {visible: false, x: 0, y: 0},
      insertSubmenuRow: {visible: false, x: 0, y: 0},
      promptScene: 0,
      returnPrompt: 0,
      promptData: null,
      
      /* ---------------------------- THEME --------------------------- */
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
      },
      /* ---------------------------- THEME --------------------------- */
    };
  },

  methods: {
    async createTableHTML(pageNum = this.currentPage) {
      try {
        const response = await axios.post('/api/table/get-page', {
          filename: this.filename,
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

    selectCell(colIndex, rowIndex) {
      this.selectedCell = { col: colIndex, row: rowIndex };
      this.editingCell = { col: null, row: null };
      
      this.selectedColumn = null;
      this.selectedRow = null;
    },

    editCell(colIndex, rowIndex) {
      this.selectedCell = { col: colIndex, row: rowIndex };
      this.writing = this.tableData[rowIndex][colIndex] || '';
      this.editingCell = { col: colIndex, row: rowIndex };
    },

    async saveCell(rowIndex, colIndex, pageNum = this.currentPage) {
      if (this.editingCell.col === null || this.editingCell.row === null) return;
      
      const newValue = this.writing ? this.writing : '';
      this.tableData[rowIndex][colIndex] = newValue;

      this.editingCell = { col: null, row: null };

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
        await this.createTableHTML();
      }
    },

    columnClicked(columnIndex) {
      this.selectedColumn = columnIndex;
      this.selectedRow = null;
      
      this.selectedCell = { col: null, row: null };
      this.editingCell = { col: null, row: null };
    },

    rowClicked(rowIndex) {
      this.selectedRow = rowIndex;
      this.selectedColumn = null;
      
      this.selectedCell = { col: null, row: null };
      this.editingCell = { col: null, row: null };
    },

    async addColumn(pageNum = this.currentPage){
      try {  
        this.colcount++;
          await axios.post('/api/table/add-column', {
            filename: this.filename,
            pagenum: pageNum
          });

      } catch (error) {
        console.error("Error saving cell:", error);
        await this.createTableHTML();
      }
    },

    async insertColumn(insertTargetColIndex){
      try{
        await axios.post('/api/table/insert-column', {
          filename: this.filename,
          pagenum: this.currentPage,
          targetcolindex: insertTargetColIndex
        });
        await this.createTableHTML();
        
      } catch (error) {
        console.error("Error inserting column:", error);
      }
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
        console.error("Error saving cell:", error);
        await this.createTableHTML();
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
        console.error("Error saving cell:", error);
        await this.createTableHTML();
      }
    },

    async insertRow(insertTargetRow){
      try{
        await axios.post('/api/table/insert-row', {
          filename: this.filename,
          pagenum: this.currentPage,
          targetrowindex: insertTargetRow
        });
        await this.createTableHTML();

      } catch (error) {
        console.error("Error inserting row:", error);
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
        console.error("Error saving cell:", error);
        await this.createTableHTML();
      }
    },

    async addPage(){
      try {
        this.totalpages++;
        await axios.post('/api/table/add-page', { filename: this.filename, pagenum: this.totalpages });
        this.currentPage = this.totalpages;
      } catch (error) {
        console.error("Error saving cell:", error);
        await this.createTableHTML();
      }
    },

    async changePageTo(newPageNum){
      this.currentPage = newPageNum;
      await this.createTableHTML();
    },

    exitToFiles(){
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
    openContextMenuCell(event, colIndex, rowIndex) {
      this.closeContextMenuCol();
      this.closeContextMenuRow();
      this.closeInsertSubmenuCol();
      this.closeInsertSubmenuRow();
      this.contextMenuCell.x = event.clientX;
      this.contextMenuCell.y = event.clientY;
      this.contextMenuCell.visible = true;
      this.contextMenuCellColIndex = colIndex;
      this.contextMenuCellRowIndex = rowIndex;
    },
    
    closeContextMenuCell() {
      this.contextMenuCell.visible = false;
      this.closeInsertSubmenuCol();
    },

    openContextMenuCol(event, index) {
      this.closeContextMenuCell();
      this.closeContextMenuRow();
      this.closeInsertSubmenuCol();
      this.closeInsertSubmenuRow();
      this.targetCol = index;
      this.contextMenuCol.x = event.clientX;
      this.contextMenuCol.y = event.clientY;
      this.contextMenuCol.visible = true;
    },

    closeContextMenuCol() {
      this.contextMenuCol.visible = false;
      this.closeInsertSubmenuRow();
    },

    openContextMenuRow(event, index) {
      this.closeContextMenuCell();
      this.closeContextMenuCol();
      this.closeInsertSubmenuCol();
      this.closeInsertSubmenuRow();
      this.targetRow = index;
      this.contextMenuRow.x = event.clientX;
      this.contextMenuRow.y = event.clientY;
      this.contextMenuRow.visible = true;
    },

    closeContextMenuRow() {
      this.contextMenuRow.visible = false;
    },

    openInsertSubmenuCol(){
      this.insertSubmenuCol.x = this.contextMenuCol.x + 60;
      this.insertSubmenuCol.y = this.contextMenuCol.y + 120;
      this.insertSubmenuCol.visible = true;
    },

    closeInsertSubmenuCol(){
      this.insertSubmenuCol.visible = false;
    },

    openInsertSubmenuRow(){
      this.insertSubmenuRow.x = this.contextMenuRow.x + 60;
      this.insertSubmenuRow.y = this.contextMenuRow.y + 120;
      this.insertSubmenuRow.visible = true;
    },

    closeInsertSubmenuRow(){
      this.insertSubmenuRow.visible = false;
    },
    /* ------------------------------------ CONTEXT MENU ------------------------------------ */ 


    async fillRowWith(targetRow, data){
      try {  
        await axios.post('/api/table/fill-row-with', {
          filename: this.filename,
          pagenum: this.currentPage,
          rowindex: targetRow,
          data: data
        });
        
        //empty the row client side, no request sent
        for (let colIndex = 0; colIndex < this.colcount; colIndex++) {
          this.tableData[targetRow][colIndex] = data;
        }
        
        //if an error occurs, get the actual data
      } catch (error) {
        console.error(`Error filling row with ${data}: `, error);
        await this.createTableHTML();
      }
    },

    openRowFillPrompt(targetRow) {
      this.targetRow = targetRow;
      this.targetCol = null;
      this.promptData = '';
      this.promptScene = 1;
    },

    async fillColumnWith(targetCol, data){
      try {  
        await axios.post('/api/table/fill-column-with', {
          filename: this.filename,
          pagenum: this.currentPage,
          colindex: targetCol,
          data: data
        });
        
        //empty the column client side, no request sent
        for (let rowIndex = 0; rowIndex < this.rowcount; rowIndex++) {
          this.tableData[rowIndex][targetCol] = data;
        }
        
        //if an error occurs, get the actual data
      } catch (error) {
        console.error(`Error filling column with ${data}: `, error);
        await this.createTableHTML();
      }
    },

    openColFillPrompt(targetCol) {
      this.targetCol = targetCol;
      this.targetRow = null;
      this.promptData = '';
      this.promptScene = 1;
    },

    async submitFillPrompt() {
      if (this.promptData !== null) {
        if (this.targetRow !== null && this.targetCol === null) {
          await this.fillRowWith(this.targetRow, this.promptData);
        } else if (this.targetCol !== null && this.targetRow === null) {
          await this.fillColumnWith(this.targetCol, this.promptData);
        }
      }
      this.promptScene = 0;
    },

    handleAction(action) {
      console.log(`Action clicked: ${action}`);
      this.closeContextMenuCell();
      this.closeContextMenuCol();
    },

    handleKeyDown(event) {
      if (this.promptScene === 1) return;

      /* ----------------- FULL COLUMN SELECTION KEYBOARD NAVIGATION -------------- */
      if (this.selectedColumn !== null) {
        if (event.key === 'ArrowLeft') {
          event.preventDefault();
          if (this.selectedColumn > 0) this.selectedColumn--;
          return;
        }
        if (event.key === 'ArrowRight') {
          event.preventDefault();
          if (this.selectedColumn < this.colcount - 1) this.selectedColumn++;
          return;
        }
        if (event.key === 'Backspace') {
          event.preventDefault();
          this.deleteColumn(this.selectedColumn);
          // Adjust index if the very last column is deleted
          if (this.selectedColumn >= this.colcount) {
            this.selectedColumn = this.colcount > 0 ? this.colcount - 1 : null;
          }
          return;
        }
      }

      /* ----------------- FULL ROW SELECTION KEYBOARD NAVIGATION -------------- */
      if (this.selectedRow !== null) {
        if (event.key === 'ArrowUp') {
          event.preventDefault();
          if (this.selectedRow > 0) this.selectedRow--;
          return;
        }
        if (event.key === 'ArrowDown') {
          event.preventDefault();
          if (this.selectedRow < this.rowcount - 1) this.selectedRow++;
          return;
        }
        if (event.key === 'Backspace') {
          event.preventDefault();
          this.deleteRow(this.selectedRow);
          // Adjust index if the very last row is deleted
          if (this.selectedRow >= this.rowcount) {
            this.selectedRow = this.rowcount > 0 ? this.rowcount - 1 : null;
          }
          return;
        }
      }


      /* ----------------- CELL SELECTION KEYBOARD NAVIGATION -------------- */
      if (this.selectedCell.col === null || this.selectedCell.row === null) return;

      const { col, row } = this.selectedCell;

      if (event.key === 'Enter') {
        event.preventDefault();

        if (this.editingCell.col !== null) {
          this.saveCell(row, col);
        } else {
          this.editCell(col, row);
        }
        return;
      }

      if (this.editingCell.col !== null) {
        if (event.key === 'Tab') {
          event.preventDefault();
          this.saveCell(row, col);

          if (event.shiftKey) {
            if (col > 0) this.selectCell(col - 1, row);
          } else {
            if (col < this.colcount - 1) this.selectCell(col + 1, row);
          }
        }
        return;
      }

      /* ----------------- COPY CUT PASTE -------------- */
      if (event.ctrlKey || event.metaKey) {
        if (event.key === 'c' || event.key === 'C') {
          event.preventDefault();
          this.copyCell();
        } else if (event.key === 'x' || event.key === 'X') {
          event.preventDefault();
          this.cutCell();
        } else if (event.key === 'v' || event.key === 'V') {
          event.preventDefault();
          this.pasteCell();
        }
        return;
      }

      switch (event.key) {
        case 'ArrowUp':
          event.preventDefault();
          if (row > 0) this.selectCell(col, row - 1);
          break;
        case 'ArrowDown':
          event.preventDefault();
          if (row < this.rowcount - 1) this.selectCell(col, row + 1);
          break;
        case 'ArrowLeft':
          event.preventDefault();
          if (col > 0) this.selectCell(col - 1, row);
          break;
        case 'ArrowRight':
          event.preventDefault();
          if (col < this.colcount - 1) this.selectCell(col + 1, row);
          break;
        case 'Backspace':
          event.preventDefault();
          this.clearCellData(row, col);
          break;
        case 'Tab':
          event.preventDefault();
          if (this.editingCell.col !== null) this.saveCell(row, col);
          if (event.shiftKey) {
            if (col > 0) this.selectCell(col - 1, row);
          } else {
            if (col < this.colcount - 1) this.selectCell(col + 1, row);
          }
          break;
      }
    },

    async clearCellData(rowIndex, colIndex) {
      this.tableData[rowIndex][colIndex] = '';
      try {
        await axios.post('/api/table/edit-cell', {
          filename: this.filename,
          pagenum: this.currentPage,
          rowindex: rowIndex,
          colindex: colIndex,
          data: ''
        });
      } catch (error) {
        console.error("Error clearing cell:", error);
        await this.createTableHTML();
      }
    },

    async copyCell() {
      if (this.selectedCell.col === null || this.selectedCell.row === null) return;
      const { row, col } = this.selectedCell;
      const value = this.tableData[row][col] || '';

      try {
        await navigator.clipboard.writeText(value);
        this.closeContextMenuCell();
      } catch (error) {
        console.error('Failed to copy to clipboard:', error);
      }
    },

    async cutCell() {
      if (this.selectedCell.col === null || this.selectedCell.row === null) return;
      const { row, col } = this.selectedCell;
      const value = this.tableData[row][col] || '';
      this.clearCellData(row, col);

      try {
        await navigator.clipboard.writeText(value);
        this.closeContextMenuCell();
      } catch (error) {
        console.error('Failed to cut to clipboard:', error);
      }
    },

    async pasteCell() {
      if (this.selectedCell.col === null || this.selectedCell.row === null) return;
      const { row, col } = this.selectedCell;

      try {
        const text = await navigator.clipboard.readText();

        this.tableData[row][col] = text;
        await axios.post('/api/table/edit-cell', {
          filename: this.filename,
          pagenum: this.currentPage,
          rowindex: row,
          colindex: col,
          data: text
        });

        this.closeContextMenuCell();
      } catch (err) {
        console.error('Failed to paste from clipboard:', err);
      }
    },
  },
  
  async mounted() {
    this.filename = localStorage.getItem('activeFileName');
    this.createTableHTML();

    window.addEventListener('click', this.closeContextMenuCell);
    window.addEventListener('click', this.closeContextMenuCol);
    window.addEventListener('click', this.closeContextMenuRow);
    window.addEventListener('click', this.closeInsertSubmenuCol);
    window.addEventListener('click', this.closeInsertSubmenuRow);
    window.addEventListener('keydown', this.handleKeyDown);
  },

  unmounted() {
    window.removeEventListener('click', this.closeContextMenuCell);
    window.removeEventListener('click', this.closeContextMenuCol);
    window.removeEventListener('click', this.closeContextMenuRow);
    window.addEventListener('click', this.closeInsertSubmenuCol);
    window.addEventListener('click', this.closeInsertSubmenuRow);
    window.removeEventListener('keydown', this.handleKeyDown);
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

.selected-cell,
.active-cell {
  outline: 2px solid var(--color-primary);
  outline-offset: -2px;
  position: relative;
  z-index: 9;
}

.selected-cell::after,
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

.cell-input {
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  border: none;
  outline: none;
  padding: 0 2px;
  margin: 0;
  font-size: 14px;
  font-family: inherit;
  background-color: transparent;
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
  background-color: #fcfcfc;
  border: 1px solid #c6c6c6;
  box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.15);
  border-radius: 0;
  padding: 2px 0;
  min-width: 100px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 13px;
}

.menu-header {
  padding: 4px 0px;
  font-size: 14px;
  font-weight: 600;
  color: #707070;
  text-align: center;
  user-select: none;
}

.menu-divider {
  height: 1px;
  background-color: #e5e5e5;
  margin: 4px 0;
}

.menu-item {
  padding: 5px 10px;
  cursor: pointer;
  color: #262626;
  text-align: left;
}

.menu-item:hover {
  background-color: #f2f2f2;
  color: #000000;
}

.header-highlighted {
  background-color: #d4d4d4;
  color: var(--color-primary);
  font-weight: 600;
  border-bottom: 1px solid var(--color-primary);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-box {
  background-color: #ffffff;
  padding: 24px 32px;
  border-radius: 4px;
  border: 1px solid var(--color-border-light);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  min-width: 280px;
  text-align: center;
}

.modal-box p {
  margin: 0 0 16px 0;
  font-size: 16px;
  color: var(--color-text-main);
  font-weight: 500;
}

.modal-close-btn {
  background-color: var(--color-primary);
  color: #ffffff;
  border: none;
  padding: 6px 16px;
  font-size: 13px;
  border-radius: 2px;
  cursor: pointer;
}

.modal-close-btn:hover {
  background-color: var(--color-primary-hover);
}
</style>