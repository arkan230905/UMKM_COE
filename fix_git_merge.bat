@echo off
echo Fixing stuck git merge...

REM Kill any vim processes
taskkill /F /IM vim.exe >nul 2>&1
taskkill /F /IM vi.exe >nul 2>&1

REM Set git editor to notepad
git config --global core.editor "notepad"

REM Abort current merge
git merge --abort

REM Check status
git status

echo.
echo Merge aborted. Now you can try again with:
echo git pull origin main
echo git push origin main
pause
