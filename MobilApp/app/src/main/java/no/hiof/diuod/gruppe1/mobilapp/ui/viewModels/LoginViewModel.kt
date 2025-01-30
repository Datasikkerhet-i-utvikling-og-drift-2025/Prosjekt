package no.hiof.diuod.gruppe1.mobilapp.ui.viewModels

import android.content.Context
import android.widget.Toast
import androidx.compose.runtime.mutableStateOf
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import androidx.navigation.NavController
import kotlinx.coroutines.launch
import no.hiof.diuod.gruppe1.mobilapp.common.ext.isValidEmail
import no.hiof.diuod.gruppe1.mobilapp.common.ext.isValidPassword

data class LoginUiState(
    val email: String = "",
    val password: String = "",
    val errorMessage: String = ""
)

class LoginViewModel(navController: NavController) : ViewModel() {

    var uiState = mutableStateOf(LoginUiState())
        private set

    private val email get() = uiState.value.email
    private val password get() = uiState.value.password
    private val errorMessage get() = uiState.value.errorMessage

    fun onEmailChange(newValue: String) {
        uiState.value = uiState.value.copy(email = newValue)
    }

    fun onPasswordChange(newValue: String) {
        uiState.value = uiState.value.copy(password = newValue)
    }

    fun onLoginClick(context: Context, navController: NavController) {

        if (!email.isValidEmail()) {
            Toast.makeText(context, "Invalid email", Toast.LENGTH_SHORT).show()
            uiState.value = uiState.value.copy(errorMessage = "Please insert a valid email")
            return
        }

        if (!password.isValidPassword()) {
            Toast.makeText(context, "Invalid password", Toast.LENGTH_SHORT).show()
            uiState.value = uiState.value.copy(errorMessage = "Your password should have at least six digits and include one digit, one lower case letter and one upper case letter.")
            return
        }

        viewModelScope.launch {

        }

    }

    fun onForgotPasswordClick() {
        try {
            val email = uiState.value.email
            if (email.isEmpty()) {
                uiState.value = uiState.value.copy(errorMessage = "Please enter the email address you want to reset")
                return
            }

            if (!isValidEmail(email)) {
                uiState.value = uiState.value.copy(errorMessage = "Please enter a valid email address")
                return
            }

            viewModelScope.launch {

            }
        } catch (e: Exception) {
            uiState.value = uiState.value.copy(errorMessage = "Could not send reset email")
        }
    }

    fun isValidEmail(email: String): Boolean {
        return android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()
    }
}