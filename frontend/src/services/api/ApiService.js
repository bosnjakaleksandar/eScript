class ApiService {
  constructor() {
    this.baseUrl = import.meta.env.VITE_API_BASE_URL;
  }

  async _handleResponse(response) {

    let responseData = null;
    let errorMessage = `HTTP error! Status: ${response.status}`;

    try {
      const contentType = response.headers.get("content-type");
      if (contentType && contentType.includes("application/json")) {
        if (response.status !== 204) {
          responseData = await response.json();
        }
        if (!response.ok && responseData?.error) {
          errorMessage = responseData.error;
        }
      } else if (!response.ok) {
        const textResponse = await response.text();
        errorMessage = textResponse.substring(0, 200) || errorMessage;
        console.warn(
          "Received non-JSON error response:",
          response.status,
          textResponse
        );
      }
    } catch (jsonError) {
      console.error("Failed to parse JSON response:", jsonError);
      if (!response.ok) {
        errorMessage = `HTTP error! Status: ${response.status}. Failed to parse response body.`;
      } else {
        errorMessage = "Invalid JSON received from server despite OK status.";
      }
      responseData = null;
    }

    if (!response.ok) {
      const error = new Error(errorMessage);
      error.status = response.status;
      error.data = responseData;
      throw error;
    }
    return responseData;
  }

  async get(endpoint) {
    try {
      const response = await fetch(this.baseUrl + endpoint, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        credentials: "include",
      });
      return await this._handleResponse(response);
    } catch (error) {
      console.error(`ApiService GET ${endpoint} Error:`, error.message);
      throw error;
    }
  }

  async post(endpoint, data) {
    try {
      const response = await fetch(this.baseUrl + endpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(data),
        credentials: "include",
      });
      return await this._handleResponse(response);
    } catch (error) {
      console.error(`ApiService POST ${endpoint} Error:`, error.message);
      throw error;
    }
  }

  async upload(endpoint, formData) {
    if (!(formData instanceof FormData)) {
      console.error("Data passed to upload method must be FormData.");
      throw new Error("Invalid data type for upload.");
    }
    try {
      const response = await fetch(this.baseUrl + endpoint, {
        method: "POST",
        headers: {
          Accept: "application/json",
        },
        body: formData,
        credentials: "include",
      });
      return await this._handleResponse(response);
    } catch (error) {
      console.error(`ApiService UPLOAD ${endpoint} Error:`, error);
      throw error;
    }
  }
}

export default new ApiService();
