# Tai - Terminal AI
AI from the Terminal

TAI allows you to communicate with OpenAI's GPT4  via your linux terminal.

## Installation
1. Download the Tai.phar file and make it executable.
```
chmod u+x Tai.phar
```

2. Create a symlink from from "/usr/local/bin/tai" to "Tai.phar"

Create a symlink
```
ln -s /home/derrick/Downloads/Tai.phar /usr/local/bin/tai
```

3. Run: tai install

```
tai install
```

Tai will ask you if it can create a folder within ~/.config/tai to store your conversations and OpenAI Key.

You will then need to provide your own Key from OpenAI. This is stored inside the "OpenAi.key" file and can simply be edited and changed if need be.

## Usage
```
tai Please write me some PHP code.
```

## Commands
```
tai clear   - Clears out your previous conversation and starts fresh.
tai help    - Well, help.
tai install - Creates required files and API key.
```

## Why
Because I wanted a quick way to interact with AI without having to login anywhere or open a browser. The goal of this project is to get answers from an AI as quickly as possible when needed.

## License
MIT