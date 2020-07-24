import { Component, OnInit } from '@angular/core';
import { User } from '../../models/user';
import { UserService } from '../../services/user.service';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css'],
  providers: [UserService]
})
export class RegisterComponent implements OnInit {

  // tslint:disable-next-line: variable-name
  public page_title: string;
  public user: User;
  public status: string;

  constructor(
    // tslint:disable-next-line: variable-name
    private _userService: UserService
  ) {
    this.page_title = "Regístrate";
    this.user = new User(1, '', '', 'ROLE_USER', '', '', '', '');

  }

  ngOnInit(): void {
    console.log("Componente de registro lanzado");
  }

  onSubmit(form) {
    this._userService.register(this.user).subscribe(
      response => {
        //console.log(response);
        if (response.status == "success") {
          this.status = response.status;
          form.reset();
        }
        else {
          this.status = "error";
        }

      },
      error => {
        this.status = "error";
        console.log(<any>error);
      }
    );


  }

}
