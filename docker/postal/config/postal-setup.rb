email = 'reborns@reborns.id'
password = 'R3b0rns_P0stal!'

# Create or find user
user = User.find_by(email_address: email)
unless user
  puts "Creating admin user #{email}..."
  user = User.new
  user.email_address = email
  user.first_name = 'Reborns'
  user.last_name = 'Admin'
  user.password = password
  user.password_confirmation = password
  user.admin = true
  if user.save
    puts "User created successfully."
  else
    puts "Failed to create user: #{user.errors.full_messages.join(', ')}"
    exit 1
  end
else
  puts "User #{email} already exists."
end

# Create or find organization
org = Organization.find_by(permalink: 'reborns')
unless org
  puts "Creating organization..."
  org = Organization.create!(name: 'Reborns', permalink: 'reborns', owner: user)
  puts "Organization created successfully."
else
  puts "Organization 'Reborns' already exists."
end

# Create or find server
server = Server.find_by(permalink: 'aimeos-mail')
unless server
  puts "Creating mail server..."
  server = Server.create!(organization: org, name: 'Aimeos Mail', permalink: 'aimeos-mail', mode: 'Live')
  puts "Server created successfully."
else
  puts "Server 'Aimeos Mail' already exists."
end

# Create credentials
unless server.credentials.exists?(name: 'Aimeos SMTP')
  puts "Creating SMTP credentials..."
  cred = server.credentials.create!(type: 'SMTP', name: 'Aimeos SMTP', key: SecureRandom.alphanumeric(24))
  puts "Credential created! Key: #{cred.key}"
else
  cred = server.credentials.find_by(name: 'Aimeos SMTP')
  puts "Credential 'Aimeos SMTP' already exists. Key: #{cred.key}"
end

# Create domain
unless server.domains.exists?(name: 'reborns.id')
  puts "Creating domain reborns.id..."
  server.domains.create!(name: 'reborns.id', verification_method: 'DNS')
  puts "Domain created successfully."
else
  puts "Domain reborns.id already exists."
end

puts "Initialization complete."
exit 0
