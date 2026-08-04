<template>
<div 
  @contextmenu.prevent 
  class="excel-backstage"
  :style="themeStyles"
>
  <!-- Left Sidebar -->
  <div class="sidebar">
    <div class="back-button">
      <span class="back-arrow">Excel26</span>
    </div>
    
    <div class="nav-item">Create</div>
    <div class="nav-item active">Files</div>

    <div class="sidebar-divider"></div>

    <div class="nav-item" v-on:click="changeTheme()">Options</div>
  </div>

  <!-- Main Content Area -->
  <div class="main-content">
    <h1 class="page-title">Files</h1>

    <div class="files-container">
      <div class="section-header">
        <span class="section-title">Recent Workbooks</span>
        <button class="btn btn-create">+ New Sheet</button>
      </div>

      <ul class="file-list">
        <!-- Sample File Row 1 -->
        <li class="file-item">
          <div class="file-icon">📊</div>
          <div class="file-details">
            <span class="file-name">File1</span>
          </div>
          <div class="file-actions">
            <button class="action-btn open-btn">Open</button>
            <button class="action-btn download-btn">Download</button>
            <button class="action-btn delete-btn">Delete</button>
          </div>
        </li>

        <!-- Sample File Row 2 -->
        <li class="file-item">
          <div class="file-icon">📊</div>
          <div class="file-details">
            <span class="file-name">File2</span>
          </div>
          <div class="file-actions">
            <button class="action-btn open-btn">Open</button>
            <button class="action-btn download-btn">Download</button>
            <button class="action-btn delete-btn">Delete</button>
          </div>
        </li>

        <!-- Sample File Row 3 -->
        <li class="file-item">
          <div class="file-icon">📊</div>
          <div class="file-details">
            <span class="file-name">File3</span>
          </div>
          <div class="file-actions">
            <button class="action-btn open-btn">Open</button>
            <button class="action-btn download-btn">Download</button>
            <button class="action-btn delete-btn">Delete</button>
          </div>
        </li>
      </ul>
    </div>
  </div>
</div>
</template>

<script setup>
import axios from 'axios';
</script>

<script>
export default {
  data() {
    return {
      files: [],
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
        
        openHoverBg: '#d4ffe8',
        openHoverBorder: '#107c41',
        downloadHoverBg: '#c8daff',
        downloadHoverBorder: '#2f8ade',
        deleteText: '#a80000',
        deleteHoverBg: '#fde8e8',
        deleteHoverBorder: '#a80000'
      }

    };
  },
  computed: {
    themeStyles() {
      return {
        '--color-primary': this.theme.primary,
        '--color-primary-hover': this.theme.primaryHover,
        '--color-primary-active': this.theme.primaryActive,

        '--color-sidebar-text': this.theme.sidebarText,
        '--color-bg-main': this.theme.bgMain,
        '--color-text-main': this.theme.textMain,
        '--color-text-secondary': this.theme.textSecondary,
        '--color-border-light': this.theme.borderLight,

        '--color-open-hover-bg': this.theme.openHoverBg,
        '--color-open-hover-border': this.theme.openHoverBorder,

        '--color-download-hover-bg': this.theme.downloadHoverBg,
        '--color-download-hover-border': this.theme.downloadHoverBorder,

        '--color-delete-text': this.theme.deleteText,
        '--color-delete-hover-bg': this.theme.deleteHoverBg,
        '--color-delete-hover-border': this.theme.deleteHoverBorder
      };
    }
  },

  methods: {
    changeTheme(){
        if(this.themeColor === 'green'){
            this.themeColor = 'blue';
            this.theme.primary = '#1f4e79';
            this.theme.primaryHover = '#173b5c';
            this.theme.primaryActive = '#10283e';
        }
        else if (this.themeColor === 'blue') {
            this.themeColor = 'green';
            this.theme.primary = '#107c41';
            this.theme.primaryHover = '#0e6e39';
            this.theme.primaryActive = '#0c5e30';
        } 
    }
  }
};
</script>

<style>
html, body {
  margin: 0;
  padding: 0;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  user-select: none;
}

/* Fullscreen Backstage Layout */
.excel-backstage {
  display: flex;
  width: 100vw;
  height: 100vh;
  background-color: var(--color-bg-main);
}

/* Left Sidebar */
.sidebar {
  width: 140px;
  background-color: var(--color-primary);
  color: var(--color-sidebar-text);
  display: flex;
  flex-direction: column;
  padding-top: 10px;
}

.back-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 50px;
  margin: 10px 0 20px 35px;
  border-radius: 50%;
  border: 2px solid var(--color-sidebar-text);
  cursor: default;
}

.back-arrow {
  font-size: 20px;
  font-weight: bold;
}

.nav-item {
  padding: 12px 24px;
  font-size: 15px;
  cursor: pointer;
  transition: background-color 0.1s ease;
}

.nav-item:hover {
  background-color: var(--color-primary-hover);
}

.nav-item.active {
  background-color: var(--color-primary-active);
  font-weight: 600;
}

.sidebar-divider {
  margin-top: auto;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  margin-bottom: 10px;
}

/* Main Content Section */
.main-content {
  flex: 1;
  padding: 40px 60px;
  overflow-y: auto;
  background-color: var(--color-bg-main);
}

.page-title {
  font-size: 42px;
  font-weight: 300;
  color: var(--color-text-main);
  margin: 0 0 30px 0;
}

.files-container {
  max-width: 800px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--color-border-light);
}

.section-title {
  font-size: 18px;
  color: var(--color-text-secondary);
  font-weight: 600;
}

.btn-create {
  background-color: var(--color-primary);
  color: var(--color-sidebar-text);
  border: none;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 2px;
  cursor: pointer;
}

.btn-create:hover {
  background-color: var(--color-primary-hover);
}

/* File Item List */
.file-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.file-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-radius: 2px;
  border: 2px solid transparent;
  transition: border-color 0.15s ease;
}

.file-item:hover {
  border-color: var(--color-primary-hover);
}

.file-icon {
  font-size: 24px;
  margin-right: 16px;
}

.file-details {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.file-name {
  font-size: 15px;
  font-weight: 600;
  color: var(--color-text-main);
}

.file-actions {
  display: flex;
  gap: 8px;
  opacity: 0.85;
}

.file-item:hover .file-actions {
  opacity: 1;
}

.action-btn {
  padding: 6px 14px;
  font-size: 13px;
  font-weight: 500;
  border: 1px solid #c8c8c8;
  background-color: #ffffff;
  color: var(--color-text-main);
  border-radius: 2px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.open-btn:hover {
  background-color: var(--color-open-hover-bg);
  border-color: var(--color-open-hover-border);
}

.download-btn:hover {
  background-color: var(--color-download-hover-bg);
  border-color: var(--color-download-hover-border);
}

.delete-btn {
  color: var(--color-delete-text);
}

.delete-btn:hover {
  background-color: var(--color-delete-hover-bg);
  border-color: var(--color-delete-hover-border);
}
</style>