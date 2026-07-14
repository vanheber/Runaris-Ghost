# Runaris Ghost Guide - v1.1

Runaris Ghost is an immersive writing environment designed for authors seeking to organize the chaos of literary creation into a structured journey.

## 0. Getting Started and Setup
When launching the application for the first time, you will go through a professional onboarding flow:
*   **Language**: Choose between Portuguese (BR/PT), English, or Spanish for the interface.
*   **Profile and Security**: Set your author name and choose whether to protect your manuscripts with a local password.
*   **AI (Optional)**: Configure your Google Gemini API key to enable co-writing features.

## 1. Project Management
Each project represents a single work (book or series). On the main screen, you can manage your stories and configure the **Book Cover**.
*   **Covers**: We recommend using images in 1600x2560 proportion (Amazon KDP/Apple Books standard). The system automatically optimizes weight and quality (JPG).

## 2. Manuscript
The heart of your book. Organized in a tree structure:
*   **Sections**: Used to group chapters or parts of the book. You can create chapters directly from a section using the `+` icon in the tree.
*   **Chapters**: Continuous narrative blocks. You can create scenes directly from a chapter using the `+` icon.
*   **Root Scenes**: Use the "New Scene" button at the top of the tree to create independent files (Prologues, Forewords) outside sections.
*   **Planning**: Each chapter has a toggleable "Planning" mode where you can draft beats before writing the final version.
*   **Clean Editor**: When creating a new item, the editor starts completely blank. The title is managed in the side tree to avoid content duplication in the manuscript.

### 2.1. Literary Mode and Ghost Formatting
The editor has an intelligent visual layer called **Ghost Formatting**:
*   **Immersive Images**: Image tags `![alt](url)` appear as elegant thumbnails within the text.
*   **Quick Swap**: Click any image in the editor to open the gallery and choose a replacement instantly.
*   **X-Ray Mode**: Need to see raw Markdown code? Use the **Markdown (X-Ray)** button in the toolbar. All formatting tools remain active in this mode.

## 3. Worldbuilding
Organize the pillars of your narrative through interactive cards:
*   **Characters**: Record appearance, motivations, and secrets.
*   **Geography**: Describe scenarios, cities, and environmental rules.
*   **Objects**: Catalog important items, relics, or tools.
*   **Connections**: The system generates a **Relationship Graph**, allowing you to visualize how characters intersect and interact with scenarios and objects.

## 4. World Bible
The central repository of your universe's "Truth".
*   **Lore**: Deep descriptions of your world's rules.
*   **AI Recap**: A narrative summary that the AI keeps updated to help you not lose the thread in long plots.

## 5. Export and Sovereign Backup
Runaris Ghost prioritizes the sovereignty of your data. You can export your work at any time in professional or raw formats:
*   **Reading Formats**: Generate **ePub** (Kindle), **PDF** (Print), or a **Web Package** (HTML). The export is **recursive**, preserving the entire hierarchy of Sections, Chapters, and Scenes defined in your side tree.
*   **Human-Readable Backup**: Generates a structured ZIP file with all content in **pure Markdown**. Unlike technical backups, files here use the **actual names** of scenes and categories, making it easy to port to any other editor (like Obsidian or Notion).
*   **Snapshots & Rollback**: You can create "Snapshots" — restoration points that capture the exact state of the project's database and files. If something goes wrong or you regret a change, you can instantly **Rollback** to a previous state.
*   **External Restore**: Allows importing a ZIP backup file to restore the project or move it between different Runaris Ghost installations.

## 6. Artificial Intelligence (Magic Buttons)
Runaris Ghost uses **Google Gemini 2.5** technology to act as your co-author:
*   **Ghost Writer**: In *Writing* mode, the AI drafts literary paragraphs based on your planning and world lore. If existing text is present, a confirmation prompt appears to prevent accidental overwriting.
*   **Suggest Ideas**: In *Planning* mode, the AI suggests conflict points, objectives, and plot twists to structure your scene.
*   **Reviewer**: Corrects Brazilian Portuguese grammar and removes "AI Slop" patterns (cliches, improper em-dashes, adverbs in dialogue, generic openings). Has checkboxes to control what to review: grammar only, anti-slop only, or both.
*   **Bible Sync**: The AI reads your manuscript and generates automatic narrative summaries. The button turns green when a summary already exists.

---
*Note: This guide serves both as a user manual and as context for artificial intelligence systems that assist with writing.*
