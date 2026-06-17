class AppError extends Error {
  constructor(message, statusCode = 500, details = undefined) {
    super(message);
    this.name = 'AppError';
    this.statusCode = statusCode;
    this.details = details;
  }
}

class AuthError extends AppError {
  constructor(message, statusCode = 401, details = undefined) {
    super(message, statusCode, details);
    this.name = 'AuthError';
  }
}

module.exports = {
  AppError,
  AuthError,
};
