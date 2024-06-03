import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';

@Pipe({
  name: 'nl2br',
  standalone: true
})
export default class Nl2brPipe implements PipeTransform {
  constructor(
    private readonly sanitizer: DomSanitizer
  ) {}

  transform(value: string): SafeHtml {
    return this.sanitizer.bypassSecurityTrustHtml(value.replace(/\n/g, '<br />'));
  }
}
