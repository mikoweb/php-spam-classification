import { Injectable } from '@angular/core';
import axios, { CreateAxiosDefaults } from 'axios';
import TranslatorService from '@app/core/application/translator/TranslatorService';
import { environment } from '../../../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class MlClient {
  private client: any;

  constructor(
    private readonly translator: TranslatorService
  ) {
    this.init();
  }

  public get method() {
    return this.client;
  }

  public async getValidationError(errorResponse: any): Promise<string> {
    const detail = errorResponse?.data?.detail;

    if (errorResponse.status === 422 && typeof detail === 'string' && detail.length > 0) {
      return detail;
    } else {
      return await this.translator.get('ml_client.common_validation_error');
    }
  }

  private init() {
    const apiPath: string = '/api';
    this.client = axios.create({
      baseURL: (environment.mlApiBaseUrl + apiPath).replace(`/${apiPath}`, apiPath)
    } as CreateAxiosDefaults);
  }
}
