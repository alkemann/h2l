# H2L - microframework

Welcome to H2L a micro framework for PHP 7.1+

## Structure

### Core (alkemann/h2l)
- Connections
- Dispatch
- Environment
- Log
- Message
- Remote
- Request
- Response
- Route
- Router
- Session

### Interfaces (alkemann/h2l/interfaces)
- Route
- Session
- Source

### Exceptions (alkemann/h2l/exceptions)
- ConfigMissing
- ConnectionError
- InvalidUrl

### Supporting (alkemann/h2l/*)
- data
    - MongoDB
    - MySQL
- response
    - Error
    - Json
    - Page
- traits
    - Model
    - Entity
- util
    - ArrayManipulations
    - Chain
    - Http
