# Use Node.js 18 as the base image
FROM node:18

# Set the working directory to the root of the project
WORKDIR /usr/src/app

# Copy package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application files
COPY . .

# Command to run the watch script
CMD ["npm", "run", "watch"]
