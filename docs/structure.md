# Structure

## Core (alkemann/h2l)

 - [Connections](classes/Connections.md)
 - [Dispatch](classes/Dispatch.md)
 - [Environment](classes/Environment.md)
 - [Log](classes/Log.md)
 - [Message](classes/Message.md)
 - [Request](classes/Request.md)
 - [Remote](classes/Remote.md)
 - [Response](classes/Response.md)
 - [Route](classes/Route.md)
 - [Router](classes/Router.md)
 - [Session](classes/Session.md)

### Interfaces (alkemann/h2l/interfaces)

- Route
- Router
- Session
- Source

### Exceptions (alkemann/h2l/exceptions)

- ConfigMissing
- ConnectionError
- CurlFailure
- EmptyChainError
- InvalidCallback
- InvalidUrl
- NoRouteSetError

### Supporting (alkemann/h2l/*)

- data
    - MongoDB
    - MySQL
- response
    - Error
    - Html
    - Json
    - Page
    - Text
- traits
    - Entity
    - Model
- util
    - ArrayManipulations
    - Chain
    - Http
