#!/bin/bash

# Build script for WP Magic Link Auth plugin
# Version 1.0
# Date: October 23, 2025

# Terminal colors
GREEN="\033[0;32m"
YELLOW="\033[1;33m"
RED="\033[0;31m"
NC="\033[0m" # No Color

# Get the script directory path
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PLUGIN_DIR="$SCRIPT_DIR"
BUILD_DIR="$PLUGIN_DIR/build"
TEMP_DIR="$BUILD_DIR/wp-magic-link-auth"
PLUGIN_NAME="wp-magic-link-auth"
VERSION=$(grep "Version:" "$PLUGIN_DIR/wp-magic-link-auth.php" | awk -F': ' '{print $2}' | tr -d '\r')

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}  WP Magic Link Auth Plugin Packager   ${NC}"
echo -e "${YELLOW}========================================${NC}"
echo -e "${GREEN}Plugin Version:${NC} $VERSION"
echo -e "${GREEN}Plugin Directory:${NC} $PLUGIN_DIR"

# Cleanup function
cleanup() {
    echo -e "\n${YELLOW}Cleaning temporary directories...${NC}"
    rm -rf "$BUILD_DIR"
    echo -e "${GREEN}Done!${NC}"
}

# Error handling
handle_error() {
    echo -e "\n${RED}Error during packaging. Operation aborted.${NC}"
    cleanup
    exit 1
}

# Trap to catch errors
trap 'handle_error' ERR

# Create build directory
echo -e "\n${YELLOW}Creating build directory...${NC}"
mkdir -p "$TEMP_DIR"

# Skip Gutenberg blocks compilation (not applicable for this plugin)
# echo -e "\n${YELLOW}Compiling Gutenberg blocks...${NC}"
# cd "$PLUGIN_DIR/blocks/gutenberg"
# npx webpack --mode=production
# echo -e "${GREEN}Compilation completed.${NC}"

# List of files and directories to include
echo -e "\n${YELLOW}Copying essential files...${NC}"
INCLUDE_DIRS=(
    "admin"
    "assets"
    "includes"
)

INCLUDE_FILES=(
    "wp-magic-link-auth.php"
    "LICENSE"
    "README.md"
)

# Copy directories to temporary directory
for dir in "${INCLUDE_DIRS[@]}"; do
    mkdir -p "$TEMP_DIR/$(dirname "$dir")"
    cp -R "$PLUGIN_DIR/$dir" "$TEMP_DIR/$(dirname "$dir")/"
    echo -e "${GREEN}Copied directory:${NC} $dir"
done

# Copy individual files
for file in "${INCLUDE_FILES[@]}"; do
    cp "$PLUGIN_DIR/$file" "$TEMP_DIR/$file"
    echo -e "${GREEN}Copied file:${NC} $file"
done

# Remove unnecessary files
echo -e "\n${YELLOW}Removing unnecessary files...${NC}"
find "$TEMP_DIR" -name ".DS_Store" -delete
find "$TEMP_DIR" -name "*.map" -delete
find "$TEMP_DIR" -name "node_modules" -type d -exec rm -rf {} +

# Create final ZIP file
echo -e "\n${YELLOW}Creating ZIP file...${NC}"
cd "$BUILD_DIR"
zip -r "${PLUGIN_NAME}-${VERSION}.zip" "$PLUGIN_NAME"
echo -e "${GREEN}ZIP file created:${NC} ${PLUGIN_NAME}-${VERSION}.zip"

# Final information
echo -e "\n${YELLOW}========================================${NC}"
echo -e "${GREEN}Package created successfully!${NC}"
echo -e "${GREEN}Path:${NC} $BUILD_DIR/${PLUGIN_NAME}-${VERSION}.zip"

# Clean temporary directories but keep the ZIP file
echo -e "\n${YELLOW}Cleaning temporary directories...${NC}"
rm -rf "$TEMP_DIR"
echo -e "${GREEN}Done!${NC}"

exit 0